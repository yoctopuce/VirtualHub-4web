'use strict';

// (C) 1995-2013 Jean-loup Gailly and Mark Adler
// (C) 2014-2017 Vitaly Puzrin and Andrey Tupitsin
//
// This software is provided 'as-is', without any express or implied
// warranty. In no event will the authors be held liable for any damages
// arising from the use of this software.
//
// Permission is granted to anyone to use this software for any purpose,
// including commercial applications, and to alter it and redistribute it
// freely, subject to the following restrictions:
//
// 1. The origin of this software must not be misrepresented; you must not
//   claim that you wrote the original software. If you use this software
//   in a product, an acknowledgment in the product documentation would be
//   appreciated but is not required.
// 2. Altered source versions must be plainly marked as such, and must not be
//   misrepresented as being the original software.
// 3. This notice may not be removed or altered from any source distribution.

import * as ZLIB from "./zlibfull.js";
import {zlib_adler32, zlib_constants, zlib_crc32, zlib_Pako_trees} from "./zlibfull.js";

interface deflateFct {(s: zlib_DeflateState, flush: number): void}

class zlib_config
    /* Values for max_lazy_match, good_match and max_chain_length, depending on
     * the desired pack level (0..9). The values given below have been tuned to
     * exclude worst case performance for pathological files. Better values may be
     * found for specific files.
     */
{
    public readonly good_length: number;
    public readonly max_lazy: number;
    public readonly nice_length: number;
    public readonly max_chain: number;
    public readonly func: deflateFct;

    constructor(good_length: number, max_lazy: number, nice_length: number, max_chain: number, func: deflateFct)
    {
        this.good_length = good_length;
        this.max_lazy = max_lazy;
        this.nice_length = nice_length;
        this.max_chain = max_chain;
        this.func = func;
    }
}

export class zlib_DeflateState
{
    public strm: any = null;            /* pointer back to this zlib stream */
    public status: number = 0;            /* as the name implies */
    public pending_buf: Uint8Array | null = null;      /* output still pending */
    public pending_buf_size: number = 0;  /* size of pending_buf */
    public pending_out: number = 0;       /* next pending byte to output to the stream */
    public pending: number = 0;           /* nb of bytes in the pending buffer */
    public wrap: number = 0;              /* bit 0 true for zlib, bit 1 true for gzip */
    public gzhead: any = null;         /* gzip header information to write */
    public gzindex: number = 0;           /* where in extra, name, or comment */
    public method: number = zlib_constants.Z_DEFLATED; /* can only be DEFLATED */
    public last_flush: number = -1;   /* value of flush param for previous deflate call */

    public w_size: number = 0;  /* LZ77 window size (32K by default) */
    public w_bits: number = 0;  /* log2(w_size)  (8..16) */
    public w_mask: number = 0;  /* w_size - 1 */

    public window: any = null;
    /* Sliding window. Input bytes are read into the second half of the window,
     * and move to the first half later to keep a dictionary of at least wSize
     * bytes. With this organization, matches are limited to a distance of
     * wSize-MAX_MATCH bytes, but this ensures that IO is always
     * performed with a length multiple of the block size.
     */

    public window_size: number = 0;
    /* Actual size of window: 2*wSize, except when the user input buffer
     * is directly used as sliding window.
     */

    public prev: any = null;
    /* Link to older string with same hash index. To limit the size of this
     * array to 64K, this link is maintained only for the last 32K strings.
     * An index in this array is thus a window index modulo 32K.
     */

    public head: Uint16Array | null = null;   /* Heads of the hash chains or NIL. */

    public ins_h: number = 0;       /* hash index of string to be inserted */
    public hash_size: number = 0;   /* number of elements in hash table */
    public hash_bits: number = 0;   /* log2(hash_size) */
    public hash_mask: number = 0;   /* hash_size-1 */

    public hash_shift: number = 0;
    /* Number of bits by which ins_h must be shifted at each input
     * step. It must be such that after MIN_MATCH steps, the oldest
     * byte no longer takes part in the hash key, that is:
     *   hash_shift * MIN_MATCH >= hash_bits
     */

    public block_start: number = 0;
    /* Window position at the beginning of the current output block. Gets
     * negative when the window is moved backwards.
     */

    public match_length: number = 0;      /* length of best match */
    public prev_match: number = 0;        /* previous match */
    public match_available: number = 0;   /* set if previous match exists */
    public strstart: number = 0;          /* start of string to insert */
    public match_start: number = 0;       /* start of matching string */
    public lookahead: number = 0;         /* number of valid bytes ahead in window */

    public prev_length: number = 0;
    /* Length of the best match at previous step. Matches not greater than this
     * are discarded. This is used in the lazy match evaluation.
     */

    public max_chain_length: number = 0;
    /* To speed up deflation, hash chains are never searched beyond this
     * length.  A higher limit improves compression ratio but degrades the
     * speed.
     */

    public max_lazy_match: number = 0;
    /* Attempt to find a better match only when the current match is strictly
     * smaller than this value. This mechanism is used only for compression
     * levels >= 4.
     */
// That's alias to max_lazy_match, don't use directly
//this.max_insert_length = 0;
    /* Insert new strings in the hash table only if the match length is not
     * greater than this length. This saves time but degrades compression.
     * max_insert_length is used only for compression levels <= 3.
     */

    public level: number = 0;     /* compression level (1..9) */
    public strategy: number = 0;  /* favor or force Huffman coding*/

    public good_match: number = 0;
    /* Use a faster search when the previous match is longer than this */

    public nice_match: number = 0; /* Stop searching when current match exceeds this */

    /* used by trees.c: */

    /* Didn't use ct_data typedef below to suppress compiler warning */

// struct ct_data_s dyn_ltree[HEAP_SIZE];   /* literal and length tree */
// struct ct_data_s dyn_dtree[2*D_CODES+1]; /* distance tree */
// struct ct_data_s bl_tree[2*BL_CODES+1];  /* Huffman tree for bit lengths */

// Use flat array of DOUBLE size, with interleaved fata,
// because JS does not support effective
    public dyn_ltree: Uint16Array = new Uint16Array(ZLIB.zlib_Pako_trees.HEAP_SIZE * 2);
    public dyn_dtree: Uint16Array = new Uint16Array((2 * ZLIB.zlib_Pako_trees.D_CODES + 1) * 2);
    public bl_tree: Uint16Array = new Uint16Array((2 * ZLIB.zlib_Pako_trees.BL_CODES + 1) * 2);

    public l_desc: any = null;         /* desc. for literal tree */
    public d_desc: any = null;         /* desc. for distance tree */
    public bl_desc: any = null;         /* desc. for bit length tree */

//ush bl_count[MAX_BITS+1];
    public bl_count: Uint16Array = new Uint16Array(ZLIB.zlib_Pako_trees.MAX_BITS + 1);
    /* number of codes at each bit length for an optimal tree */

//int heap[2*L_CODES+1];      /* heap used to build the Huffman trees */
    public heap: Uint16Array = new Uint16Array(2 * ZLIB.zlib_Pako_trees.L_CODES + 1);  /* heap used to build the Huffman trees */

    public heap_len: number = 0;               /* number of elements in the heap */
    public heap_max: number = 0;               /* element of largest frequency */
    /* The sons of heap[n] are heap[2*n] and heap[2*n+1]. heap[0] is not used.
     * The same heap array is used to build all trees.
     */

    public depth: Uint16Array = new Uint16Array(2 * ZLIB.zlib_Pako_trees.L_CODES + 1); //uch depth[2*L_CODES+1];

    /* Depth of each subtree used as tie breaker for trees of equal frequency
     */

    public l_buf: number = 0;          /* buffer index for literals or lengths */

    public lit_bufsize: number = 0;
    /* Size of match buffer for literals/lengths.  There are 4 reasons for
     * limiting lit_bufsize to 64K:
     *   - frequencies can be kept in 16 bit counters
     *   - if compression is not successful for the first block, all input
     *     data is still in the window so we can still emit a stored block even
     *     when input comes from standard input.  (This can also be done for
     *     all blocks if lit_bufsize is not greater than 32K.)
     *   - if compression is not successful for a file smaller than 64K, we can
     *     even emit a stored file instead of a stored block (saving 5 bytes).
     *     This is applicable only for zip (not gzip or zlib).
     *   - creating new Huffman trees less frequently may not provide fast
     *     adaptation to changes in the input data statistics. (Take for
     *     example a binary file with poorly compressible code followed by
     *     a highly compressible string table.) Smaller buffer sizes give
     *     fast adaptation but have of course the overhead of transmitting
     *     trees more frequently.
     *   - I can't count above 4
     */

    public last_lit: number = 0;      /* running index in l_buf */

    public d_buf: number = 0;
    /* Buffer index for distances. To simplify the code, d_buf and l_buf have
     * the same number of elements. To use different lengths, an extra flag
     * array would be necessary.
     */

    public opt_len: number = 0;       /* bit length of current block with optimal trees */
    public static_len: number = 0;    /* bit length of current block with static trees */
    public matches: number = 0;       /* number of string matches in current block */
    public insert: number = 0;        /* bytes at end of window left to insert */

    public bi_buf: number = 0;
    /* Output buffer. bits are inserted starting at the bottom (least
     * significant bits).
     */
    public bi_valid: number = 0;
    /* Number of valid bits in bi_buf.  All bits above the last valid bit
     * are always zero.
     */

// Used for window memory init. We safely ignore it for JS. That makes
// sense only for pointers and memory check tools.
//this.high_water = 0;
    /* High water mark offset in window for initialized bytes -- bytes above
     * this are set to zero in order to avoid memory check warnings when
     * longest match routines access bytes past the input.  This is then
     * updated to the new high water mark.
     */
    constructor()
    {
        ZLIB.zlib_Pako_trees.zero(this.dyn_ltree);
        ZLIB.zlib_Pako_trees.zero(this.dyn_dtree);
        ZLIB.zlib_Pako_trees.zero(this.bl_tree);
        ZLIB.zlib_Pako_trees.zero(this.heap);
        ZLIB.zlib_Pako_trees.zero(this.depth);
    }
}

export class zlib_deflate
{

    private static readonly MAX_MEM_LEVEL: number = 9;
    /* Maximum value for memLevel in deflateInit2 */
    private static readonly MAX_WBITS: number = 15;
    /* 32K LZ77 window */
    private static readonly DEF_MEM_LEVEL: number = 8;

    private static readonly LENGTH_CODES: number = 29;
    /* number of length codes, not counting the special END_BLOCK code */
    private static readonly LITERALS: number = 256;
    /* number of literal bytes 0..255 */
    private static readonly L_CODES: number = zlib_deflate.LITERALS + 1 + zlib_deflate.LENGTH_CODES;
    /* number of Literal or Length codes, including the END_BLOCK code */
    private static readonly D_CODES: number = 30;
    /* number of distance codes */
    private static readonly BL_CODES: number = 19;
    /* number of codes used to transfer the bit lengths */
    private static readonly HEAP_SIZE: number = 2 * zlib_deflate.L_CODES + 1;
    /* maximum heap size */
    private static readonly MAX_BITS: number = 15;
    /* All codes must not exceed MAX_BITS bits */

    private static readonly MIN_MATCH: number = 3;
    private static readonly MAX_MATCH: number = 258;
    private static readonly MIN_LOOKAHEAD: number = (zlib_deflate.MAX_MATCH + zlib_deflate.MIN_MATCH + 1);

    private static readonly PRESET_DICT: number = 0x20;

    private static readonly INIT_STATE: number = 42;
    private static readonly EXTRA_STATE: number = 69;
    private static readonly NAME_STATE: number = 73;
    private static readonly COMMENT_STATE: number = 91;
    private static readonly HCRC_STATE: number = 103;
    private static readonly BUSY_STATE: number = 113;
    private static readonly FINISH_STATE: number = 666;

    private static readonly BS_NEED_MORE: number = 1; /* block not completed, need more input or more output */
    private static readonly BS_BLOCK_DONE: number = 2; /* block flush performed */
    private static readonly BS_FINISH_STARTED: number = 3; /* finish started, need only more output at next deflate */
    private static readonly BS_FINISH_DONE: number = 4; /* finish done, accept no more input or output */

    private static readonly OS_CODE = 0x03; // Unix :) . Don't detect, use this default.

    private static err(strm: ZLIB.zlib_ZStream, errorCode: number)
    {
        strm.msg = ZLIB.zlib_messages.msg(errorCode);
        return errorCode;
    };

    private static rank(f: number) { return ((f) << 1) - ((f) > 4 ? 9 : 0); }

    private zero(buf: Uint8Array)
    {
        let len: number = buf.length;
        while (--len >= 0)
        { buf[len] = 0; }
    }

    /* eslint-disable new-cap */
    private static HASH_ZLIB(s: zlib_DeflateState, prev: number, data: number): number {return ((prev << s.hash_shift) ^ data) & s.hash_mask;}
// This hash causes less collisions, https://github.com/nodeca/pako/issues/135
// But breaks binary compatibility
//let HASH_FAST = (s, prev, data) => ((prev << 8) + (prev >> 8) + (data << 4)) & s.hash_mask;
    private static HASH(s: zlib_DeflateState, prev: number, data: number): number { return ((prev << s.hash_shift) ^ data) & s.hash_mask;}

    /* =========================================================================
     * Flush as much pending output as possible. All deflate() output goes
     * through this function so some applications may wish to modify it
     * to avoid allocating a large strm->output buffer and copying into it.
     * (See also read_buf()).
     */
    private static flush_pending(strm: ZLIB.zlib_ZStream)
    {
        const s = <zlib_DeflateState>strm.state;

        //_tr_flush_bits(s);
        let len: number = s.pending;
        if (len > strm.avail_out)
        {
            len = strm.avail_out;
        }
        if (len === 0)
        { return; }

        strm.output.set((<Uint8Array>s.pending_buf).subarray(s.pending_out, s.pending_out + len), strm.next_out);
        strm.next_out += len;
        s.pending_out += len;
        strm.total_out += len;
        strm.avail_out -= len;
        s.pending -= len;
        if (s.pending === 0)
        {
            s.pending_out = 0;
        }
    }

    private static flush_block_only(s: zlib_DeflateState, last: boolean)
    {
        ZLIB.zlib_Pako_trees._tr_flush_block(s, (s.block_start >= 0 ? s.block_start : -1), s.strstart - s.block_start, last);
        s.block_start = s.strstart;
        zlib_deflate.flush_pending(s.strm);
    }

    private static put_byte(s: zlib_DeflateState, b: number)
    {
        (<Uint8Array>s.pending_buf)[s.pending++] = b;
    }

    /* =========================================================================
     * Put a short in the pending buffer. The 16-bit value is put in MSB order.
     * IN assertion: the stream state is correct and there is enough room in
     * pending_buf.
     */
    private static putShortMSB(s: zlib_DeflateState, b: number)
    {

        //  put_byte(s, (Byte)(b >> 8));
//  put_byte(s, (Byte)(b & 0xff));
        (<Uint8Array>s.pending_buf)[s.pending++] = (b >>> 8) & 0xff;
        (<Uint8Array>s.pending_buf)[s.pending++] = b & 0xff;
    }

    /* ===========================================================================
     * Read a new buffer from the current input stream, update the adler32
     * and total number of bytes read.  All deflate() input goes through
     * this function so some applications may wish to modify it to avoid
     * allocating a large strm->input buffer and copying from it.
     * (See also flush_pending()).
     */
    private static read_buf(strm: ZLIB.zlib_ZStream, buf: Uint8Array, start: number, size: number)
    {

        let len: number = strm.avail_in;

        if (len > size)
        { len = size; }
        if (len === 0)
        { return 0; }

        strm.avail_in -= len;

        // zmemcpy(buf, strm->next_in, len);
        buf.set(strm.input.subarray(strm.next_in, strm.next_in + len), start);
        if ((<zlib_DeflateState>strm.state).wrap === 1)
        {
            strm.adler = ZLIB.zlib_adler32.adler32(strm.adler, buf, len, start);
        }

        else if ((<zlib_DeflateState>strm.state).wrap === 2)
        {
            strm.adler = ZLIB.zlib_crc32.crc32(strm.adler, buf, len, start);
        }

        strm.next_in += len;
        strm.total_in += len;

        return len;
    }

    /* ===========================================================================
     * Set match_start to the longest match starting at the given string and
     * return its length. Matches shorter or equal to prev_length are discarded,
     * in which case the result is equal to prev_length and match_start is
     * garbage.
     * IN assertions: cur_match is the head of the hash chain for the current
     *   string (strstart) and its distance is <= MAX_DIST, and prev_length >= 1
     * OUT assertion: the match length is not greater than s->lookahead.
     */
    private static longest_match(s: zlib_DeflateState, cur_match: number)
    {

        let chain_length: number = s.max_chain_length;      /* max hash chain length */
        let scan: number = s.strstart; /* current string */
        let match: number;                       /* matched string */
        let len: number;                           /* length of current match */
        let best_len: number = s.prev_length;              /* best match length so far */
        let nice_match: number = s.nice_match;             /* stop if match long enough */
        const limit = (s.strstart > (s.w_size - zlib_deflate.MIN_LOOKAHEAD)) ?
            s.strstart - (s.w_size - zlib_deflate.MIN_LOOKAHEAD) : 0/*NIL*/;

        const _win = s.window; // shortcut

        const wmask = s.w_mask;
        const prev = s.prev;

        /* Stop when cur_match becomes <= limit. To simplify the code,
         * we prevent matches with the string of window index 0.
         */

        const strend = s.strstart + ZLIB.zlib_Pako_trees.MAX_MATCH;
        let scan_end1 = _win[scan + best_len - 1];
        let scan_end = _win[scan + best_len];

        /* The code is optimized for HASH_BITS >= 8 and MAX_MATCH-2 multiple of 16.
         * It is easy to get rid of this optimization if necessary.
         */
        // Assert(s->hash_bits >= 8 && MAX_MATCH == 258, "Code too clever");

        /* Do not waste too much time if we already have a good match: */
        if (s.prev_length >= s.good_match)
        {
            chain_length >>= 2;
        }
        /* Do not look for matches beyond the end of the input. This is necessary
         * to make deflate deterministic.
         */
        if (nice_match > s.lookahead)
        { nice_match = s.lookahead; }

        // Assert((ulg)s->strstart <= s->window_size-MIN_LOOKAHEAD, "need lookahead");

        do
        {
            // Assert(cur_match < s->strstart, "no future");
            match = cur_match;

            /* Skip to next match if the match length cannot increase
             * or if the match length is less than 2.  Note that the checks below
             * for insufficient lookahead only occur occasionally for performance
             * reasons.  Therefore uninitialized memory will be accessed, and
             * conditional jumps will be made that depend on those values.
             * However the length of the match is limited to the lookahead, so
             * the output of deflate is not affected by the uninitialized values.
             */

            if (_win[match + best_len] !== scan_end ||
                _win[match + best_len - 1] !== scan_end1 ||
                _win[match] !== _win[scan] ||
                _win[++match] !== _win[scan + 1])
            {
                continue;
            }

            /* The check at best_len-1 can be removed because it will be made
             * again later. (This heuristic is not always a win.)
             * It is not necessary to compare scan[2] and match[2] since they
             * are always equal when the other bytes match, given that
             * the hash keys are equal and that HASH_BITS >= 8.
             */
            scan += 2;
            match++;
            // Assert(*scan == *match, "match[2]?");

            /* We check for insufficient lookahead only every 8th comparison;
             * the 256th check will be made at strstart+258.
             */
            do
            {
                /*jshint noempty:false*/
            } while (_win[++scan] === _win[++match] && _win[++scan] === _win[++match] &&
            _win[++scan] === _win[++match] && _win[++scan] === _win[++match] &&
            _win[++scan] === _win[++match] && _win[++scan] === _win[++match] &&
            _win[++scan] === _win[++match] && _win[++scan] === _win[++match] &&
            scan < strend);

            // Assert(scan <= s->window+(unsigned)(s->window_size-1), "wild scan");

            len = ZLIB.zlib_Pako_trees.MAX_MATCH - (strend - scan);
            scan = strend - ZLIB.zlib_Pako_trees.MAX_MATCH;

            if (len > best_len)
            {
                s.match_start = cur_match;
                best_len = len;
                if (len >= nice_match)
                {
                    break;
                }
                scan_end1 = _win[scan + best_len - 1];
                scan_end = _win[scan + best_len];
            }
        } while ((cur_match = prev[cur_match & wmask]) > limit && --chain_length !== 0);

        if (best_len <= s.lookahead)
        {
            return best_len;
        }
        return s.lookahead;
    }

    /* ===========================================================================
     * Fill the window when the lookahead becomes insufficient.
     * Updates strstart and lookahead.
     *
     * IN assertion: lookahead < MIN_LOOKAHEAD
     * OUT assertions: strstart <= window_size-MIN_LOOKAHEAD
     *    At least one byte has been read, or avail_in == 0; reads are
     *    performed for at least two bytes (required for the zip translate_eol
     *    option -- not supported here).
     */
    private static fill_window(s: zlib_DeflateState)
    {

        const _w_size: number = s.w_size;
        let p: number
        let n: number
        let m: number
        let more: number
        let str: number;

        //Assert(s->lookahead < MIN_LOOKAHEAD, "already enough lookahead");

        do
        {
            more = s.window_size - s.lookahead - s.strstart;

            // JS ints have 32 bit, block below not needed
            /* Deal with !@#$% 64K limit: */
            //if (sizeof(int) <= 2) {
            //    if (more == 0 && s->strstart == 0 && s->lookahead == 0) {
            //        more = wsize;
            //
            //  } else if (more == (unsigned)(-1)) {
            //        /* Very unlikely, but possible on 16 bit machine if
            //         * strstart == 0 && lookahead == 1 (input done a byte at time)
            //         */
            //        more--;
            //    }
            //}

            /* If the window is almost full and there is insufficient lookahead,
             * move the upper half to the lower one to make room in the upper half.
             */
            if (s.strstart >= _w_size + (_w_size - zlib_deflate.MIN_LOOKAHEAD))
            {

                s.window.set(s.window.subarray(_w_size, _w_size + _w_size), 0);
                s.match_start -= _w_size;
                s.strstart -= _w_size;
                /* we now have strstart >= MAX_DIST */
                s.block_start -= _w_size;

                /* Slide the hash table (could be avoided with 32 bit values
                 at the expense of memory usage). We slide even when level == 0
                 to keep the hash table consistent if we switch back to level > 0
                 later. (Using level 0 permanently is not an optimal usage of
                 zlib, so we don't care about this pathological case.)
                 */

                n = s.hash_size;
                p = n;

                do
                {
                    m = (<Uint16Array>s.head)[--p];
                    (<Uint16Array>s.head)[p] = (m >= _w_size ? m - _w_size : 0);
                } while (--n);

                n = _w_size;
                p = n;

                do
                {
                    m = s.prev[--p];
                    s.prev[p] = (m >= _w_size ? m - _w_size : 0);
                    /* If n is not on any hash chain, prev[n] is garbage but
                     * its value will never be used.
                     */
                } while (--n);

                more += _w_size;
            }
            if (s.strm.avail_in === 0)
            {
                break;
            }

            /* If there was no sliding:
             *    strstart <= WSIZE+MAX_DIST-1 && lookahead <= MIN_LOOKAHEAD - 1 &&
             *    more == window_size - lookahead - strstart
             * => more >= window_size - (MIN_LOOKAHEAD-1 + WSIZE + MAX_DIST-1)
             * => more >= window_size - 2*WSIZE + 2
             * In the BIG_MEM or MMAP case (not yet supported),
             *   window_size == input_size + MIN_LOOKAHEAD  &&
             *   strstart + s->lookahead <= input_size => more >= MIN_LOOKAHEAD.
             * Otherwise, window_size == 2*WSIZE so more >= 2.
             * If there was sliding, more >= WSIZE. So in all cases, more >= 2.
             */
            //Assert(more >= 2, "more < 2");
            n = zlib_deflate.read_buf(s.strm, s.window, s.strstart + s.lookahead, more);
            s.lookahead += n;

            /* Initialize the hash value now that we have some input: */
            if (s.lookahead + s.insert >= zlib_Pako_trees.MIN_MATCH)
            {
                str = s.strstart - s.insert;
                s.ins_h = s.window[str];

                /* UPDATE_HASH(s, s->ins_h, s->window[str + 1]); */
                s.ins_h = zlib_deflate.HASH(s, s.ins_h, s.window[str + 1]);
//# if MIN_MATCH != 3
//        Call update_hash() MIN_MATCH-3 more times
//# end if
                while (s.insert)
                {
                    /* UPDATE_HASH(s, s->ins_h, s->window[str + MIN_MATCH-1]); */
                    s.ins_h = zlib_deflate.HASH(s, s.ins_h, s.window[str + zlib_Pako_trees.MIN_MATCH - 1]);

                    s.prev[str & s.w_mask] = (<Uint16Array>s.head)[s.ins_h];
                    (<Uint16Array>s.head)[s.ins_h] = str;
                    str++;
                    s.insert--;
                    if (s.lookahead + s.insert < zlib_Pako_trees.MIN_MATCH)
                    {
                        break;
                    }
                }
            }
            /* If the whole input has less than MIN_MATCH bytes, ins_h is garbage,
             * but this is not important since only literal bytes will be emitted.
             */

        } while (s.lookahead < zlib_deflate.MIN_LOOKAHEAD && s.strm.avail_in !== 0);

        /* If the WIN_INIT bytes after the end of the current data have never been
         * written, then zero those bytes in order to avoid memory check reports of
         * the use of uninitialized (or uninitialised as Julian writes) bytes by
         * the longest match routines.  Update the high water mark for the next
         * time through here.  WIN_INIT is set to MAX_MATCH since the longest match
         * routines allow scanning to strstart + MAX_MATCH, ignoring lookahead.
         */
//  if (s.high_water < s.window_size) {
//    const curr = s.strstart + s.lookahead;
//    let init = 0;
//
//    if (s.high_water < curr) {
//      /* Previous high water mark below current data -- zero WIN_INIT
//       * bytes or up to end of window, whichever is less.
//       */
//      init = s.window_size - curr;
//      if (init > WIN_INIT)
//        init = WIN_INIT;
//      zmemzero(s->window + curr, (unsigned)init);
//      s->high_water = curr + init;
//    }
//    else if (s->high_water < (ulg)curr + WIN_INIT) {
//      /* High water mark at or above current data, but below current data
//       * plus WIN_INIT -- zero out to current data plus WIN_INIT, or up
//       * to end of window, whichever is less.
//       */
//      init = (ulg)curr + WIN_INIT - s->high_water;
//      if (init > s->window_size - s->high_water)
//        init = s->window_size - s->high_water;
//      zmemzero(s->window + s->high_water, (unsigned)init);
//      s->high_water += init;
//    }
//  }
//
//  Assert((ulg)s->strstart <= s->window_size - MIN_LOOKAHEAD,
//    "not enough room for search");
    };

    /* ===========================================================================
     * Copy without compression as much as possible from the input stream, return
     * the current block state.
     * This function does not insert new strings in the dictionary since
     * uncompressible data is probably not useful. This function is used
     * only for the level=0 compression option.
     * NOTE: this function should be optimized to avoid extra copying from
     * window to pending_buf.
     */
    private static deflate_stored(s: zlib_DeflateState, flush: number)
    {

        /* Stored blocks are limited to 0xffff bytes, pending_buf is limited
         * to pending_buf_size, and each stored block has a 5 byte header:
         */
        let max_block_size: number = 0xffff;

        if (max_block_size > s.pending_buf_size - 5)
        {
            max_block_size = s.pending_buf_size - 5;
        }

        /* Copy as much as possible from input to output: */
        for (; ;)
        {
            /* Fill the window as much as possible: */
            if (s.lookahead <= 1)
            {

                //Assert(s->strstart < s->w_size+MAX_DIST(s) ||
                //  s->block_start >= (long)s->w_size, "slide too late");
//      if (!(s.strstart < s.w_size + (s.w_size - MIN_LOOKAHEAD) ||
//        s.block_start >= s.w_size)) {
//        throw  new Error("slide too late");
//      }

                zlib_deflate.fill_window(s);
                if (s.lookahead === 0 && flush === zlib_constants.Z_NO_FLUSH)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }

                if (s.lookahead === 0)
                {
                    break;
                }
                /* flush the current block */
            }
            //Assert(s->block_start >= 0L, "block gone");
//    if (s.block_start < 0) throw new Error("block gone");

            s.strstart += s.lookahead;
            s.lookahead = 0;

            /* Emit a stored block if pending_buf will be full: */
            const max_start = s.block_start + max_block_size;

            if (s.strstart === 0 || s.strstart >= max_start)
            {
                /* strstart == 0 is possible when wraparound on 16-bit machine */
                s.lookahead = s.strstart - max_start;
                s.strstart = max_start;
                /*** FLUSH_BLOCK(s, 0); ***/
                zlib_deflate.flush_block_only(s, false);
                if (s.strm.avail_out === 0)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
                /***/

            }
            /* Flush if we may have to slide, otherwise block_start may become
             * negative and the data will be gone:
             */
            if (s.strstart - s.block_start >= (s.w_size - zlib_deflate.MIN_LOOKAHEAD))
            {
                /*** FLUSH_BLOCK(s, 0); ***/
                zlib_deflate.flush_block_only(s, false);
                if (s.strm.avail_out === 0)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
                /***/
            }
        }

        s.insert = 0;

        if (flush === zlib_constants.Z_FINISH)
        {
            /*** FLUSH_BLOCK(s, 1); ***/
            zlib_deflate.flush_block_only(s, true);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_FINISH_STARTED;
            }
            /***/
            return zlib_deflate.BS_FINISH_DONE;
        }

        if (s.strstart > s.block_start)
        {
            /*** FLUSH_BLOCK(s, 0); ***/
            zlib_deflate.flush_block_only(s, false);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_NEED_MORE;
            }
            /***/
        }

        return zlib_deflate.BS_NEED_MORE;
    };

    /* ===========================================================================
     * Compress as much as possible from the input stream, return the current
     * block state.
     * This function does not perform lazy evaluation of matches and inserts
     * new strings in the dictionary only for unmatched strings or for short
     * matches. It is used only for the fast compression options.
     */
    private static deflate_fast = (s: zlib_DeflateState, flush: number) =>
    {

        let hash_head: number;        /* head of the hash chain */
        let bflush: boolean;           /* set if current block must be flushed */

        for (; ;)
        {
            /* Make sure that we always have enough lookahead, except
             * at the end of the input file. We need MAX_MATCH bytes
             * for the next match, plus MIN_MATCH bytes to insert the
             * string following the next match.
             */
            if (s.lookahead < zlib_deflate.MIN_LOOKAHEAD)
            {
                zlib_deflate.fill_window(s);
                if (s.lookahead < zlib_deflate.MIN_LOOKAHEAD && flush === zlib_constants.Z_NO_FLUSH)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
                if (s.lookahead === 0)
                {
                    break; /* flush the current block */
                }
            }

            /* Insert the string window[strstart .. strstart+2] in the
             * dictionary, and set hash_head to the head of the hash chain:
             */
            hash_head = 0/*NIL*/;
            if (s.lookahead >= zlib_Pako_trees.MIN_MATCH)
            {
                /*** INSERT_STRING(s, s.strstart, hash_head); ***/
                s.ins_h = zlib_deflate.HASH(s, s.ins_h, s.window[s.strstart + zlib_Pako_trees.MIN_MATCH - 1]);
                hash_head = s.prev[s.strstart & s.w_mask] = (<Uint16Array>s.head)[s.ins_h];
                (<Uint16Array>s.head)[s.ins_h] = s.strstart;
                /***/
            }

            /* Find the longest match, discarding those <= prev_length.
             * At this point we have always match_length < MIN_MATCH
             */
            if (hash_head !== 0/*NIL*/ && ((s.strstart - hash_head) <= (s.w_size - zlib_deflate.MIN_LOOKAHEAD)))
            {
                /* To simplify the code, we prevent matches with the string
                 * of window index 0 (in particular we have to avoid a match
                 * of the string with itself at the start of the input file).
                 */
                s.match_length = zlib_deflate.longest_match(s, hash_head);
                /* longest_match() sets match_start */
            }
            if (s.match_length >= zlib_Pako_trees.MIN_MATCH)
            {
                // check_match(s, s.strstart, s.match_start, s.match_length); // for debug only

                /*** _tr_tally_dist(s, s.strstart - s.match_start,
                 s.match_length - MIN_MATCH, bflush); ***/
                bflush = zlib_Pako_trees._tr_tally(s, s.strstart - s.match_start, s.match_length - zlib_Pako_trees.MIN_MATCH);

                s.lookahead -= s.match_length;

                /* Insert new strings in the hash table only if the match length
                 * is not too large. This saves time but degrades compression.
                 */
                if (s.match_length <= s.max_lazy_match/*max_insert_length*/ && s.lookahead >= zlib_Pako_trees.MIN_MATCH)
                {
                    s.match_length--; /* string at strstart already in table */
                    do
                    {
                        s.strstart++;
                        /*** INSERT_STRING(s, s.strstart, hash_head); ***/
                        s.ins_h = zlib_deflate.HASH(s, s.ins_h, s.window[s.strstart + zlib_Pako_trees.MIN_MATCH - 1]);
                        hash_head = s.prev[s.strstart & s.w_mask] = (<Uint16Array>s.head)[s.ins_h];
                        (<Uint16Array>s.head)[s.ins_h] = s.strstart;
                        /***/
                        /* strstart never exceeds WSIZE-MAX_MATCH, so there are
                         * always MIN_MATCH bytes ahead.
                         */
                    } while (--s.match_length !== 0);
                    s.strstart++;
                }
                else
                {
                    s.strstart += s.match_length;
                    s.match_length = 0;
                    s.ins_h = s.window[s.strstart];
                    /* UPDATE_HASH(s, s.ins_h, s.window[s.strstart+1]); */
                    s.ins_h = zlib_deflate.HASH(s, s.ins_h, s.window[s.strstart + 1]);

//# if MIN_MATCH != 3
//                Call UPDATE_HASH() MIN_MATCH-3 more times
//# end if
                    /* If lookahead < MIN_MATCH, ins_h is garbage, but it does not
                     * matter since it will be recomputed at next deflate call.
                     */
                }
            }
            else
            {
                /* No match, output a literal byte */
                //Tracevv((stderr,"%c", s.window[s.strstart]));
                /*** _tr_tally_lit(s, s.window[s.strstart], bflush); ***/
                bflush = zlib_Pako_trees._tr_tally(s, 0, s.window[s.strstart]);

                s.lookahead--;
                s.strstart++;
            }
            if (bflush)
            {
                /*** FLUSH_BLOCK(s, 0); ***/
                zlib_deflate.flush_block_only(s, false);
                if (s.strm.avail_out === 0)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
                /***/
            }
        }
        s.insert = ((s.strstart < (zlib_Pako_trees.MIN_MATCH - 1)) ? s.strstart : zlib_Pako_trees.MIN_MATCH - 1);
        if (flush === zlib_constants.Z_FINISH)
        {
            /*** FLUSH_BLOCK(s, 1); ***/
            zlib_deflate.flush_block_only(s, true);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_FINISH_STARTED;
            }
            /***/
            return zlib_deflate.BS_FINISH_DONE;
        }
        if (s.last_lit)
        {
            /*** FLUSH_BLOCK(s, 0); ***/
            zlib_deflate.flush_block_only(s, false);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_NEED_MORE;
            }
            /***/
        }
        return zlib_deflate.BS_BLOCK_DONE;
    };

    /* ===========================================================================
     * Same as above, but achieves better compression. We use a lazy
     * evaluation for matches: a match is finally adopted only if there is
     * no better match at the next window position.
     */
    private static deflate_slow(s: zlib_DeflateState, flush: number)
    {

        let hash_head: number;          /* head of hash chain */
        let bflush: boolean;              /* set if current block must be flushed */

        let max_insert: number;

        /* Process the input block. */
        for (; ;)
        {
            /* Make sure that we always have enough lookahead, except
             * at the end of the input file. We need MAX_MATCH bytes
             * for the next match, plus MIN_MATCH bytes to insert the
             * string following the next match.
             */
            if (s.lookahead < zlib_deflate.MIN_LOOKAHEAD)
            {
                zlib_deflate.fill_window(s);
                if (s.lookahead < zlib_deflate.MIN_LOOKAHEAD && flush === zlib_constants.Z_NO_FLUSH)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
                if (s.lookahead === 0)
                { break; } /* flush the current block */
            }

            /* Insert the string window[strstart .. strstart+2] in the
             * dictionary, and set hash_head to the head of the hash chain:
             */
            hash_head = 0/*NIL*/;
            if (s.lookahead >= zlib_Pako_trees.MIN_MATCH)
            {
                /*** INSERT_STRING(s, s.strstart, hash_head); ***/
                s.ins_h = zlib_deflate.HASH(s, s.ins_h, s.window[s.strstart + zlib_Pako_trees.MIN_MATCH - 1]);
                hash_head = s.prev[s.strstart & s.w_mask] = (<Uint16Array>s.head)[s.ins_h];
                (<Uint16Array>s.head)[s.ins_h] = s.strstart;
                /***/
            }

            /* Find the longest match, discarding those <= prev_length.
             */
            s.prev_length = s.match_length;
            s.prev_match = s.match_start;
            s.match_length = zlib_Pako_trees.MIN_MATCH - 1;

            if (hash_head !== 0/*NIL*/ && s.prev_length < s.max_lazy_match &&
                s.strstart - hash_head <= (s.w_size - zlib_deflate.MIN_LOOKAHEAD)/*MAX_DIST(s)*/)
            {
                /* To simplify the code, we prevent matches with the string
                 * of window index 0 (in particular we have to avoid a match
                 * of the string with itself at the start of the input file).
                 */
                s.match_length = zlib_deflate.longest_match(s, hash_head);
                /* longest_match() sets match_start */

                if (s.match_length <= 5 &&
                    (s.strategy === ZLIB.zlib_constants.Z_FILTERED || (s.match_length === zlib_Pako_trees.MIN_MATCH && s.strstart - s.match_start > 4096/*TOO_FAR*/)))
                {

                    /* If prev_match is also MIN_MATCH, match_start is garbage
                     * but we will ignore the current match anyway.
                     */
                    s.match_length = zlib_Pako_trees.MIN_MATCH - 1;
                }
            }
            /* If there was a match at the previous step and the current
             * match is not better, output the previous match:
             */
            if (s.prev_length >= zlib_Pako_trees.MIN_MATCH && s.match_length <= s.prev_length)
            {
                max_insert = s.strstart + s.lookahead - zlib_Pako_trees.MIN_MATCH;
                /* Do not insert strings in hash table beyond this. */

                //check_match(s, s.strstart-1, s.prev_match, s.prev_length);

                /***_tr_tally_dist(s, s.strstart - 1 - s.prev_match,
                 s.prev_length - MIN_MATCH, bflush);***/
                bflush = zlib_Pako_trees._tr_tally(s, s.strstart - 1 - s.prev_match, s.prev_length - zlib_Pako_trees.MIN_MATCH);
                /* Insert in hash table all strings up to the end of the match.
                 * strstart-1 and strstart are already inserted. If there is not
                 * enough lookahead, the last two strings are not inserted in
                 * the hash table.
                 */
                s.lookahead -= s.prev_length - 1;
                s.prev_length -= 2;
                do
                {
                    if (++s.strstart <= max_insert)
                    {
                        /*** INSERT_STRING(s, s.strstart, hash_head); ***/
                        s.ins_h = zlib_deflate.HASH(s, s.ins_h, s.window[s.strstart + zlib_Pako_trees.MIN_MATCH - 1]);
                        hash_head = s.prev[s.strstart & s.w_mask] = (<Uint16Array>s.head)[s.ins_h];
                        (<Uint16Array>s.head)[s.ins_h] = s.strstart;
                        /***/
                    }
                } while (--s.prev_length !== 0);
                s.match_available = 0;
                s.match_length = zlib_Pako_trees.MIN_MATCH - 1;
                s.strstart++;

                if (bflush)
                {
                    /*** FLUSH_BLOCK(s, 0); ***/
                    zlib_deflate.flush_block_only(s, false);
                    if (s.strm.avail_out === 0)
                    {
                        return zlib_deflate.BS_NEED_MORE;
                    }
                    /***/
                }

            }
            else if (s.match_available)
            {
                /* If there was no match at the previous position, output a
                 * single literal. If there was a match but the current match
                 * is longer, truncate the previous match to a single literal.
                 */
                //Tracevv((stderr,"%c", s->window[s->strstart-1]));
                /*** _tr_tally_lit(s, s.window[s.strstart-1], bflush); ***/
                bflush = zlib_Pako_trees._tr_tally(s, 0, s.window[s.strstart - 1]);

                if (bflush)
                {
                    /*** FLUSH_BLOCK_ONLY(s, 0) ***/
                    zlib_deflate.flush_block_only(s, false);
                    /***/
                }
                s.strstart++;
                s.lookahead--;
                if (s.strm.avail_out === 0)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
            }
            else
            {
                /* There is no previous match to compare with, wait for
                 * the next step to decide.
                 */
                s.match_available = 1;
                s.strstart++;
                s.lookahead--;
            }
        }
        //Assert (flush != Pako_constants.Z_NO_FLUSH, "no flush?");
        if (s.match_available)
        {
            //Tracevv((stderr,"%c", s->window[s->strstart-1]));
            /*** _tr_tally_lit(s, s.window[s.strstart-1], bflush); ***/
            bflush = zlib_Pako_trees._tr_tally(s, 0, s.window[s.strstart - 1]);

            s.match_available = 0;
        }
        s.insert = s.strstart < zlib_Pako_trees.MIN_MATCH - 1 ? s.strstart : zlib_Pako_trees.MIN_MATCH - 1;
        if (flush === zlib_constants.Z_FINISH)
        {
            /*** FLUSH_BLOCK(s, 1); ***/
            zlib_deflate.flush_block_only(s, true);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_FINISH_STARTED;
            }
            /***/
            return zlib_deflate.BS_FINISH_DONE;
        }
        if (s.last_lit)
        {
            /*** FLUSH_BLOCK(s, 0); ***/
            zlib_deflate.flush_block_only(s, false);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_NEED_MORE;
            }
            /***/
        }

        return zlib_deflate.BS_BLOCK_DONE;
    };

    /* ===========================================================================
     * For Pako_constants.Z_RLE, simply look for runs of bytes, generate matches only of distance
     * one.  Do not maintain a hash table.  (It will be regenerated if this run of
     * deflate switches away from Pako_constants.Z_RLE.)
     */
    private static deflate_rle(s: zlib_DeflateState, flush: number)
    {

        let bflush: boolean;            /* set if current block must be flushed */
        let prev: number;              /* byte at distance one to match */
        let scan: number;
        let strend: number;      /* scan goes up to strend for length of run */

        const _win = s.window;

        for (; ;)
        {
            /* Make sure that we always have enough lookahead, except
             * at the end of the input file. We need MAX_MATCH bytes
             * for the longest run, plus one for the unrolled loop.
             */
            if (s.lookahead <= zlib_Pako_trees.MAX_MATCH)
            {
                zlib_deflate.fill_window(s);
                if (s.lookahead <= zlib_Pako_trees.MAX_MATCH && flush === zlib_constants.Z_NO_FLUSH)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
                if (s.lookahead === 0)
                { break; } /* flush the current block */
            }

            /* See how many times the previous byte repeats */
            s.match_length = 0;
            if (s.lookahead >= zlib_Pako_trees.MIN_MATCH && s.strstart > 0)
            {
                scan = s.strstart - 1;
                prev = _win[scan];
                if (prev === _win[++scan] && prev === _win[++scan] && prev === _win[++scan])
                {
                    strend = s.strstart + zlib_Pako_trees.MAX_MATCH;
                    do
                    {
                        /*jshint noempty:false*/
                    } while (prev === _win[++scan] && prev === _win[++scan] &&
                    prev === _win[++scan] && prev === _win[++scan] &&
                    prev === _win[++scan] && prev === _win[++scan] &&
                    prev === _win[++scan] && prev === _win[++scan] &&
                    scan < strend);
                    s.match_length = zlib_Pako_trees.MAX_MATCH - (strend - scan);
                    if (s.match_length > s.lookahead)
                    {
                        s.match_length = s.lookahead;
                    }
                }
                //Assert(scan <= s->window+(uInt)(s->window_size-1), "wild scan");
            }

            /* Emit match if have run of MIN_MATCH or longer, else emit literal */
            if (s.match_length >= zlib_Pako_trees.MIN_MATCH)
            {
                //check_match(s, s.strstart, s.strstart - 1, s.match_length);

                /*** _tr_tally_dist(s, 1, s.match_length - MIN_MATCH, bflush); ***/
                bflush = zlib_Pako_trees._tr_tally(s, 1, s.match_length - zlib_Pako_trees.MIN_MATCH);

                s.lookahead -= s.match_length;
                s.strstart += s.match_length;
                s.match_length = 0;
            }
            else
            {
                /* No match, output a literal byte */
                //Tracevv((stderr,"%c", s->window[s->strstart]));
                /*** _tr_tally_lit(s, s.window[s.strstart], bflush); ***/
                bflush = zlib_Pako_trees._tr_tally(s, 0, s.window[s.strstart]);

                s.lookahead--;
                s.strstart++;
            }
            if (bflush)
            {
                /*** FLUSH_BLOCK(s, 0); ***/
                zlib_deflate.flush_block_only(s, false);
                if (s.strm.avail_out === 0)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
                /***/
            }
        }
        s.insert = 0;
        if (flush === zlib_constants.Z_FINISH)
        {
            /*** FLUSH_BLOCK(s, 1); ***/
            zlib_deflate.flush_block_only(s, true);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_FINISH_STARTED;
            }
            /***/
            return zlib_deflate.BS_FINISH_DONE;
        }
        if (s.last_lit)
        {
            /*** FLUSH_BLOCK(s, 0); ***/
            zlib_deflate.flush_block_only(s, false);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_NEED_MORE;
            }
            /***/
        }
        return zlib_deflate.BS_BLOCK_DONE;
    };

    /* ===========================================================================
     * For Pako_constants.Z_HUFFMAN_ONLY, do not look for matches.  Do not maintain a hash table.
     * (It will be regenerated if this run of deflate switches away from Huffman.)
     */
    private static deflate_huff(s: zlib_DeflateState, flush: number)
    {

        let bflush;             /* set if current block must be flushed */

        for (; ;)
        {
            /* Make sure that we have a literal to write. */
            if (s.lookahead === 0)
            {
                zlib_deflate.fill_window(s);
                if (s.lookahead === 0)
                {
                    if (flush === zlib_constants.Z_NO_FLUSH)
                    {
                        return zlib_deflate.BS_NEED_MORE;
                    }
                    break;      /* flush the current block */
                }
            }

            /* Output a literal byte */
            s.match_length = 0;
            //Tracevv((stderr,"%c", s->window[s->strstart]));
            /*** _tr_tally_lit(s, s.window[s.strstart], bflush); ***/
            bflush = zlib_Pako_trees._tr_tally(s, 0, s.window[s.strstart]);
            s.lookahead--;
            s.strstart++;
            if (bflush)
            {
                /*** FLUSH_BLOCK(s, 0); ***/
                zlib_deflate.flush_block_only(s, false);
                if (s.strm.avail_out === 0)
                {
                    return zlib_deflate.BS_NEED_MORE;
                }
                /***/
            }
        }
        s.insert = 0;
        if (flush === zlib_constants.Z_FINISH)
        {
            /*** FLUSH_BLOCK(s, 1); ***/
            zlib_deflate.flush_block_only(s, true);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_FINISH_STARTED;
            }
            /***/
            return zlib_deflate.BS_FINISH_DONE;
        }
        if (s.last_lit)
        {
            /*** FLUSH_BLOCK(s, 0); ***/
            zlib_deflate.flush_block_only(s, false);
            if (s.strm.avail_out === 0)
            {
                return zlib_deflate.BS_NEED_MORE;
            }
            /***/
        }
        return zlib_deflate.BS_BLOCK_DONE;
    }

    private static configuration_table: zlib_config[] = [
        /*      good lazy nice chain */
        new zlib_config(0, 0, 0, 0, zlib_deflate.deflate_stored),          /* 0 store only */
        new zlib_config(4, 4, 8, 4, zlib_deflate.deflate_fast),            /* 1 max speed, no lazy matches */
        new zlib_config(4, 5, 16, 8, zlib_deflate.deflate_fast),           /* 2 */
        new zlib_config(4, 6, 32, 32, zlib_deflate.deflate_fast),          /* 3 */

        new zlib_config(4, 4, 16, 16, zlib_deflate.deflate_slow),          /* 4 lazy matches */
        new zlib_config(8, 16, 32, 32, zlib_deflate.deflate_slow),         /* 5 */
        new zlib_config(8, 16, 128, 128, zlib_deflate.deflate_slow),       /* 6 */
        new zlib_config(8, 32, 128, 256, zlib_deflate.deflate_slow),       /* 7 */
        new zlib_config(32, 128, 258, 1024, zlib_deflate.deflate_slow),    /* 8 */
        new zlib_config(32, 258, 258, 4096, zlib_deflate.deflate_slow)     /* 9 max compression */
    ];

    /* ===========================================================================
     * Initialize the "longest match" routines for a new zlib stream
     */
    private static lm_init(s: zlib_DeflateState)
    {

        s.window_size = 2 * s.w_size;

        /*** CLEAR_HASH(s); ***/
        zlib_Pako_trees.zero((<Uint16Array>s.head)); // Fill with NIL (= 0);

        /* Set the default configuration parameters:
         */
        s.max_lazy_match = zlib_deflate.configuration_table[s.level].max_lazy;
        s.good_match = zlib_deflate.configuration_table[s.level].good_length;
        s.nice_match = zlib_deflate.configuration_table[s.level].nice_length;
        s.max_chain_length = zlib_deflate.configuration_table[s.level].max_chain;

        s.strstart = 0;
        s.block_start = 0;
        s.lookahead = 0;
        s.insert = 0;
        s.match_length = s.prev_length = zlib_Pako_trees.MIN_MATCH - 1;
        s.match_available = 0;
        s.ins_h = 0;
    };

    public static deflateResetKeep(strm: ZLIB.zlib_ZStream)
    {

        if (!strm || !strm.state)
        {
            return zlib_deflate.err(strm, zlib_constants.Z_STREAM_ERROR);
        }

        strm.total_in = strm.total_out = 0;
        strm.data_type = zlib_constants.Z_UNKNOWN;

        let s: zlib_DeflateState = <zlib_DeflateState>strm.state;
        s.pending = 0;
        s.pending_out = 0;

        if (s.wrap < 0)
        {
            s.wrap = -s.wrap;
            /* was made negative by deflate(..., Pako_constants.Z_FINISH); */
        }
        s.status = (s.wrap ? zlib_deflate.INIT_STATE : zlib_deflate.BUSY_STATE);
        strm.adler = (s.wrap === 2) ?
            0  // crc32(0, Pako_constants.Z_NULL, 0)
            :
            1; // adler32(0, Pako_constants.Z_NULL, 0)
        s.last_flush = zlib_constants.Z_NO_FLUSH;
        zlib_Pako_trees._tr_init(s);
        return zlib_constants.Z_OK;
    };

    public static deflateReset(strm: ZLIB.zlib_ZStream)
    {

        const ret = zlib_deflate.deflateResetKeep(strm);
        if (ret === zlib_constants.Z_OK)
        {
            zlib_deflate.lm_init(<zlib_DeflateState>strm.state);
        }
        return ret;
    };

    public static deflateSetHeader(strm: ZLIB.zlib_ZStream, head: ZLIB.zlib_GZheader)
    {

        if (!strm || !strm.state)
        { return zlib_constants.Z_STREAM_ERROR; }
        if (strm.state.wrap !== 2)
        { return zlib_constants.Z_STREAM_ERROR; }
        (<zlib_DeflateState>strm.state).gzhead = head;
        return zlib_constants.Z_OK;
    }

    public static deflateInit2(strm: ZLIB.zlib_ZStream, level: number, method: number, windowBits: number, memLevel: number, strategy: number)
    {

        if (!strm)
        { // === Pako_constants.Z_NULL
            return zlib_constants.Z_STREAM_ERROR;
        }
        let wrap = 1;

        if (level === zlib_constants.Z_DEFAULT_COMPRESSION)
        {
            level = 6;
        }

        if (windowBits < 0)
        { /* suppress zlib wrapper */
            wrap = 0;
            windowBits = -windowBits;
        }

        else if (windowBits > 15)
        {
            wrap = 2;           /* write gzip wrapper instead */
            windowBits -= 16;
        }

        if (memLevel < 1 || memLevel > zlib_deflate.MAX_MEM_LEVEL || method !== zlib_constants.Z_DEFLATED ||
            windowBits < 8 || windowBits > 15 || level < 0 || level > 9 ||
            strategy < 0 || strategy > zlib_constants.Z_FIXED)
        {
            return zlib_deflate.err(strm, zlib_constants.Z_STREAM_ERROR);
        }

        if (windowBits === 8)
        {
            windowBits = 9;
        }
        /* until 256-byte window bug fixed */

        const s = new zlib_DeflateState();

        strm.state = s;
        s.strm = strm;

        s.wrap = wrap;
        s.gzhead = null;
        s.w_bits = windowBits;
        s.w_size = 1 << s.w_bits;
        s.w_mask = s.w_size - 1;

        s.hash_bits = memLevel + 7;
        s.hash_size = 1 << s.hash_bits;
        s.hash_mask = s.hash_size - 1;
        s.hash_shift = ~~((s.hash_bits + zlib_Pako_trees.MIN_MATCH - 1) / zlib_Pako_trees.MIN_MATCH);

        s.window = new Uint8Array(s.w_size * 2);
        s.head = new Uint16Array(s.hash_size);
        s.prev = new Uint16Array(s.w_size);

        // Don't need mem init magic for JS.
        //s.high_water = 0;  /* nothing written to s->window yet */

        s.lit_bufsize = 1 << (memLevel + 6); /* 16K elements by default */

        s.pending_buf_size = s.lit_bufsize * 4;

        //overlay = (ushf *) ZALLOC(strm, s->lit_bufsize, sizeof(ush)+2);
        //s->pending_buf = (uchf *) overlay;
        s.pending_buf = new Uint8Array(s.pending_buf_size);

        // It is offset from `s.pending_buf` (size is `s.lit_bufsize * 2`)
        //s->d_buf = overlay + s->lit_bufsize/sizeof(ush);
        s.d_buf = 1 * s.lit_bufsize;

        //s->l_buf = s->pending_buf + (1+sizeof(ush))*s->lit_bufsize;
        s.l_buf = (1 + 2) * s.lit_bufsize;

        s.level = level;
        s.strategy = strategy;
        s.method = method;

        return zlib_deflate.deflateReset(strm);
    };

    public static deflateInit(strm: ZLIB.zlib_ZStream, level: number)
    {

        return zlib_deflate.deflateInit2(strm, level, zlib_constants.Z_DEFLATED, zlib_deflate.MAX_WBITS, zlib_deflate.DEF_MEM_LEVEL, zlib_constants.Z_DEFAULT_STRATEGY);
    };

    public static deflate(strm: ZLIB.zlib_ZStream, flush: number)
    {

        let beg, val; // for gzip header write only

        if (!strm || !strm.state ||
            flush > zlib_constants.Z_BLOCK || flush < 0)
        {
            return strm ? zlib_deflate.err(strm, zlib_constants.Z_STREAM_ERROR) : zlib_constants.Z_STREAM_ERROR;
        }

        let s: zlib_DeflateState = <zlib_DeflateState>strm.state;

        if (!strm.output ||
            (!strm.input && strm.avail_in !== 0) ||
            (s.status === zlib_deflate.FINISH_STATE && flush !== zlib_constants.Z_FINISH))
        {
            return zlib_deflate.err(strm, (strm.avail_out === 0) ? zlib_constants.Z_BUF_ERROR : zlib_constants.Z_STREAM_ERROR);
        }

        s.strm = strm; /* just in case */
        const old_flush = s.last_flush;
        s.last_flush = flush;

        /* Write the header */
        if (s.status === zlib_deflate.INIT_STATE)
        {

            if (s.wrap === 2)
            { // GZIP header
                strm.adler = 0;  //crc32(0L, Pako_constants.Z_NULL, 0);
                zlib_deflate.put_byte(s, 31);
                zlib_deflate.put_byte(s, 139);
                zlib_deflate.put_byte(s, 8);
                if (!s.gzhead)
                { // s->gzhead == Pako_constants.Z_NULL
                    zlib_deflate.put_byte(s, 0);
                    zlib_deflate.put_byte(s, 0);
                    zlib_deflate.put_byte(s, 0);
                    zlib_deflate.put_byte(s, 0);
                    zlib_deflate.put_byte(s, 0);
                    zlib_deflate.put_byte(s, s.level === 9 ? 2 :
                        (s.strategy >= ZLIB.zlib_constants.Z_HUFFMAN_ONLY || s.level < 2 ?
                            4 : 0));
                    zlib_deflate.put_byte(s, zlib_deflate.OS_CODE);
                    s.status = zlib_deflate.BUSY_STATE;
                }
                else
                {
                    zlib_deflate.put_byte(s, (s.gzhead.text ? 1 : 0) +
                        (s.gzhead.hcrc ? 2 : 0) +
                        (!s.gzhead.extra ? 0 : 4) +
                        (!s.gzhead.name ? 0 : 8) +
                        (!s.gzhead.comment ? 0 : 16)
                    );
                    zlib_deflate.put_byte(s, s.gzhead.time & 0xff);
                    zlib_deflate.put_byte(s, (s.gzhead.time >> 8) & 0xff);
                    zlib_deflate.put_byte(s, (s.gzhead.time >> 16) & 0xff);
                    zlib_deflate.put_byte(s, (s.gzhead.time >> 24) & 0xff);
                    zlib_deflate.put_byte(s, s.level === 9 ? 2 :
                        (s.strategy >= ZLIB.zlib_constants.Z_HUFFMAN_ONLY || s.level < 2 ?
                            4 : 0));
                    zlib_deflate.put_byte(s, s.gzhead.os & 0xff);
                    if (s.gzhead.extra && s.gzhead.extra.length)
                    {
                        zlib_deflate.put_byte(s, s.gzhead.extra.length & 0xff);
                        zlib_deflate.put_byte(s, (s.gzhead.extra.length >> 8) & 0xff);
                    }
                    if (s.gzhead.hcrc)
                    {
                        strm.adler = zlib_crc32.crc32(strm.adler, <Uint8Array>s.pending_buf, s.pending, 0);
                    }
                    s.gzindex = 0;
                    s.status = zlib_deflate.EXTRA_STATE;
                }
            }
            else // DEFLATE header
            {
                let header = (zlib_constants.Z_DEFLATED + ((s.w_bits - 8) << 4)) << 8;
                let level_flags = -1;

                if (s.strategy >= ZLIB.zlib_constants.Z_HUFFMAN_ONLY || s.level < 2)
                {
                    level_flags = 0;
                }
                else if (s.level < 6)
                {
                    level_flags = 1;
                }
                else if (s.level === 6)
                {
                    level_flags = 2;
                }
                else
                {
                    level_flags = 3;
                }
                header |= (level_flags << 6);
                if (s.strstart !== 0)
                { header |= zlib_deflate.PRESET_DICT; }
                header += 31 - (header % 31);

                s.status = zlib_deflate.BUSY_STATE;
                zlib_deflate.putShortMSB(s, header);

                /* Save the adler32 of the preset dictionary: */
                if (s.strstart !== 0)
                {
                    zlib_deflate.putShortMSB(s, strm.adler >>> 16);
                    zlib_deflate.putShortMSB(s, strm.adler & 0xffff);
                }
                strm.adler = 1; // adler32(0L, Pako_constants.Z_NULL, 0);
            }
        }

//# if def GZIP
        if (s.status === zlib_deflate.EXTRA_STATE)
        {
            if (s.gzhead.extra/* != Pako_constants.Z_NULL*/)
            {
                beg = s.pending;  /* start of bytes to update crc */

                while (s.gzindex < (s.gzhead.extra.length & 0xffff))
                {
                    if (s.pending === s.pending_buf_size)
                    {
                        if (s.gzhead.hcrc && s.pending > beg)
                        {
                            strm.adler = zlib_crc32.crc32(strm.adler, <Uint8Array>s.pending_buf, s.pending - beg, beg);
                        }
                        zlib_deflate.flush_pending(strm);
                        beg = s.pending;
                        if (s.pending === s.pending_buf_size)
                        {
                            break;
                        }
                    }
                    zlib_deflate.put_byte(s, s.gzhead.extra[s.gzindex] & 0xff);
                    s.gzindex++;
                }
                if (s.gzhead.hcrc && s.pending > beg)
                {
                    strm.adler = zlib_crc32.crc32(strm.adler, <Uint8Array>s.pending_buf, s.pending - beg, beg);
                }
                if (s.gzindex === s.gzhead.extra.length)
                {
                    s.gzindex = 0;
                    s.status = zlib_deflate.NAME_STATE;
                }
            }
            else
            {
                s.status = zlib_deflate.NAME_STATE;
            }
        }
        if (s.status === zlib_deflate.NAME_STATE)
        {
            if (s.gzhead.name/* != Pako_constants.Z_NULL*/)
            {
                beg = s.pending;  /* start of bytes to update crc */
                //int val;

                do
                {
                    if (s.pending === s.pending_buf_size)
                    {
                        if (s.gzhead.hcrc && s.pending > beg)
                        {
                            strm.adler = zlib_crc32.crc32(strm.adler, <Uint8Array>s.pending_buf, s.pending - beg, beg);
                        }
                        zlib_deflate.flush_pending(strm);
                        beg = s.pending;
                        if (s.pending === s.pending_buf_size)
                        {
                            val = 1;
                            break;
                        }
                    }
                    // JS specific: little magic to add zero terminator to end of string
                    if (s.gzindex < s.gzhead.name.length)
                    {
                        val = s.gzhead.name.charCodeAt(s.gzindex++) & 0xff;
                    }
                    else
                    {
                        val = 0;
                    }
                    zlib_deflate.put_byte(s, val);
                } while (val !== 0);

                if (s.gzhead.hcrc && s.pending > beg)
                {
                    strm.adler = zlib_crc32.crc32(strm.adler, <Uint8Array>s.pending_buf, s.pending - beg, beg);
                }
                if (val === 0)
                {
                    s.gzindex = 0;
                    s.status = zlib_deflate.COMMENT_STATE;
                }
            }
            else
            {
                s.status = zlib_deflate.COMMENT_STATE;
            }
        }
        if (s.status === zlib_deflate.COMMENT_STATE)
        {
            if (s.gzhead.comment/* != Pako_constants.Z_NULL*/)
            {
                beg = s.pending;  /* start of bytes to update crc */
                //int val;

                do
                {
                    if (s.pending === s.pending_buf_size)
                    {
                        if (s.gzhead.hcrc && s.pending > beg)
                        {
                            strm.adler = zlib_crc32.crc32(strm.adler, <Uint8Array>s.pending_buf, s.pending - beg, beg);
                        }
                        zlib_deflate.flush_pending(strm);
                        beg = s.pending;
                        if (s.pending === s.pending_buf_size)
                        {
                            val = 1;
                            break;
                        }
                    }
                    // JS specific: little magic to add zero terminator to end of string
                    if (s.gzindex < s.gzhead.comment.length)
                    {
                        val = s.gzhead.comment.charCodeAt(s.gzindex++) & 0xff;
                    }
                    else
                    {
                        val = 0;
                    }
                    zlib_deflate.put_byte(s, val);
                } while (val !== 0);

                if (s.gzhead.hcrc && s.pending > beg)
                {
                    strm.adler = zlib_crc32.crc32(strm.adler, <Uint8Array>s.pending_buf, s.pending - beg, beg);
                }
                if (val === 0)
                {
                    s.status = zlib_deflate.HCRC_STATE;
                }
            }
            else
            {
                s.status = zlib_deflate.HCRC_STATE;
            }
        }
        if (s.status === zlib_deflate.HCRC_STATE)
        {
            if (s.gzhead.hcrc)
            {
                if (s.pending + 2 > s.pending_buf_size)
                {
                    zlib_deflate.flush_pending(strm);
                }
                if (s.pending + 2 <= s.pending_buf_size)
                {
                    zlib_deflate.put_byte(s, strm.adler & 0xff);
                    zlib_deflate.put_byte(s, (strm.adler >> 8) & 0xff);
                    strm.adler = 0; //crc32(0L, Pako_constants.Z_NULL, 0);
                    s.status = zlib_deflate.BUSY_STATE;
                }
            }
            else
            {
                s.status = zlib_deflate.BUSY_STATE;
            }
        }
//# end if

        /* Flush as much pending output as possible */
        if (s.pending !== 0)
        {
            zlib_deflate.flush_pending(strm);
            if (strm.avail_out === 0)
            {
                /* Since avail_out is 0, deflate will be called again with
                 * more output space, but possibly with both pending and
                 * avail_in equal to zero. There won't be anything to do,
                 * but this is not an error situation so make sure we
                 * return OK instead of BUF_ERROR at next call of deflate:
                 */
                s.last_flush = -1;
                return zlib_constants.Z_OK;
            }

            /* Make sure there is something to do and avoid duplicate consecutive
             * flushes. For repeated and useless calls with Pako_constants.Z_FINISH, we keep
             * returning Pako_constants.Z_STREAM_END instead of Pako_constants.Z_BUF_ERROR.
             */
        }
        else if (strm.avail_in === 0 && zlib_deflate.rank(flush) <= zlib_deflate.rank(old_flush) &&
            flush !== zlib_constants.Z_FINISH)
        {
            return zlib_deflate.err(strm, zlib_constants.Z_BUF_ERROR);
        }

        /* User must not provide more input after the first FINISH: */
        if (s.status === zlib_deflate.FINISH_STATE && strm.avail_in !== 0)
        {
            return zlib_deflate.err(strm, zlib_constants.Z_BUF_ERROR);
        }

        /* Start a new block or continue the current one.
         */
        if (strm.avail_in !== 0 || s.lookahead !== 0 ||
            (flush !== zlib_constants.Z_NO_FLUSH && s.status !== zlib_deflate.FINISH_STATE))
        {
            let bstate = (s.strategy === ZLIB.zlib_constants.Z_HUFFMAN_ONLY) ? zlib_deflate.deflate_huff(s, flush) :
                (s.strategy === ZLIB.zlib_constants.Z_RLE ? zlib_deflate.deflate_rle(s, flush) :
                    zlib_deflate.configuration_table[s.level].func(s, flush));

            if (bstate === zlib_deflate.BS_FINISH_STARTED || bstate === zlib_deflate.BS_FINISH_DONE)
            {
                s.status = zlib_deflate.FINISH_STATE;
            }
            if (bstate === zlib_deflate.BS_NEED_MORE || bstate === zlib_deflate.BS_FINISH_STARTED)
            {
                if (strm.avail_out === 0)
                {
                    s.last_flush = -1;
                    /* avoid BUF_ERROR next call, see above */
                }
                return zlib_constants.Z_OK;
                /* If flush != Pako_constants.Z_NO_FLUSH && avail_out == 0, the next call
                 * of deflate should use the same flush parameter to make sure
                 * that the flush is complete. So we don't have to output an
                 * empty block here, this will be done at next call. This also
                 * ensures that for a very small output buffer, we emit at most
                 * one empty block.
                 */
            }
            if (bstate === zlib_deflate.BS_BLOCK_DONE)
            {
                if (flush === ZLIB.zlib_constants.Z_PARTIAL_FLUSH)
                {
                    zlib_Pako_trees._tr_align(s);
                }
                else if (flush !== zlib_constants.Z_BLOCK)
                { /* FULL_FLUSH or SYNC_FLUSH */

                    zlib_Pako_trees._tr_stored_block(s, 0, 0, false);
                    /* For a full flush, this empty block will be recognized
                     * as a special marker by inflate_sync().
                     */
                    if (flush === zlib_constants.Z_FULL_FLUSH)
                    {
                        /*** CLEAR_HASH(s); ***/             /* forget history */
                        zlib_Pako_trees.zero(<Uint16Array>s.head); // Fill with NIL (= 0);

                        if (s.lookahead === 0)
                        {
                            s.strstart = 0;
                            s.block_start = 0;
                            s.insert = 0;
                        }
                    }
                }
                zlib_deflate.flush_pending(strm);
                if (strm.avail_out === 0)
                {
                    s.last_flush = -1; /* avoid BUF_ERROR at next call, see above */
                    return zlib_constants.Z_OK;
                }
            }
        }
        //Assert(strm->avail_out > 0, "bug2");
        //if (strm.avail_out <= 0) { throw new Error("bug2");}

        if (flush !== zlib_constants.Z_FINISH)
        { return zlib_constants.Z_OK; }
        if (s.wrap <= 0)
        { return zlib_constants.Z_STREAM_END; }

        /* Write the trailer */
        if (s.wrap === 2)
        {
            zlib_deflate.put_byte(s, strm.adler & 0xff);
            zlib_deflate.put_byte(s, (strm.adler >> 8) & 0xff);
            zlib_deflate.put_byte(s, (strm.adler >> 16) & 0xff);
            zlib_deflate.put_byte(s, (strm.adler >> 24) & 0xff);
            zlib_deflate.put_byte(s, strm.total_in & 0xff);
            zlib_deflate.put_byte(s, (strm.total_in >> 8) & 0xff);
            zlib_deflate.put_byte(s, (strm.total_in >> 16) & 0xff);
            zlib_deflate.put_byte(s, (strm.total_in >> 24) & 0xff);
        }
        else
        {
            zlib_deflate.putShortMSB(s, strm.adler >>> 16);
            zlib_deflate.putShortMSB(s, strm.adler & 0xffff);
        }

        zlib_deflate.flush_pending(strm);
        /* If avail_out is zero, the application will call deflate again
         * to flush the rest.
         */
        if (s.wrap > 0)
        { s.wrap = -s.wrap; }
        /* write the trailer only once! */
        return s.pending !== 0 ? zlib_constants.Z_OK : zlib_constants.Z_STREAM_END;
    }

    public static deflateEnd(strm: ZLIB.zlib_ZStream)
    {

        if (!strm/*== Pako_constants.Z_NULL*/ || !strm.state/*== Pako_constants.Z_NULL*/)
        {
            return zlib_constants.Z_STREAM_ERROR;
        }

        let status: number = (<zlib_DeflateState>strm.state).status;
        if (status !== zlib_deflate.INIT_STATE &&
            status !== zlib_deflate.EXTRA_STATE &&
            status !== zlib_deflate.NAME_STATE &&
            status !== zlib_deflate.COMMENT_STATE &&
            status !== zlib_deflate.HCRC_STATE &&
            status !== zlib_deflate.BUSY_STATE &&
            status !== zlib_deflate.FINISH_STATE
        )
        {
            return zlib_deflate.err(strm, zlib_constants.Z_STREAM_ERROR);
        }

        strm.state = null;

        return status === zlib_deflate.BUSY_STATE ? zlib_deflate.err(strm, zlib_constants.Z_DATA_ERROR) : zlib_constants.Z_OK;
    }

    /* =========================================================================
     * Initializes the compression dictionary from the given byte
     * sequence without producing any compressed output.
     */
    public static deflateSetDictionary(strm: ZLIB.zlib_ZStream, dictionary: Uint8Array)
    {

        let dictLength: number = dictionary.length;

        if (!strm/*== Pako_constants.Z_NULL*/ || !strm.state/*== Pako_constants.Z_NULL*/)
        {
            return zlib_constants.Z_STREAM_ERROR;
        }

        const s: zlib_DeflateState = <zlib_DeflateState>strm.state;
        const wrap = s.wrap;

        if (wrap === 2 || (wrap === 1 && s.status !== zlib_deflate.INIT_STATE) || s.lookahead)
        {
            return zlib_constants.Z_STREAM_ERROR;
        }

        /* when using zlib wrappers, compute Adler-32 for provided dictionary */
        if (wrap === 1)
        {
            /* adler32(strm->adler, dictionary, dictLength); */
            strm.adler = zlib_adler32.adler32(strm.adler, dictionary, dictLength, 0);
        }

        s.wrap = 0;   /* avoid computing Adler-32 in read_buf */

        /* if dictionary would fill window, just replace the history */
        if (dictLength >= s.w_size)
        {
            if (wrap === 0)
            {            /* already empty otherwise */
                /*** CLEAR_HASH(s); ***/
                zlib_Pako_trees.zero(<Uint16Array>s.head); // Fill with NIL (= 0);
                s.strstart = 0;
                s.block_start = 0;
                s.insert = 0;
            }
            /* use the tail */
            // dictionary = dictionary.slice(dictLength - s.w_size);
            let tmpDict = new Uint8Array(s.w_size);
            tmpDict.set(dictionary.subarray(dictLength - s.w_size, dictLength), 0);
            dictionary = tmpDict;
            dictLength = s.w_size;
        }
        /* insert dictionary into window and hash */
        const avail = strm.avail_in;
        const next = strm.next_in;
        const input = strm.input;
        strm.avail_in = dictLength;
        strm.next_in = 0;
        strm.input = dictionary;
        zlib_deflate.fill_window(s);
        while (s.lookahead >= zlib_Pako_trees.MIN_MATCH)
        {
            let str = s.strstart;
            let n = s.lookahead - (zlib_Pako_trees.MIN_MATCH - 1);
            do
            {
                /* UPDATE_HASH(s, s->ins_h, s->window[str + MIN_MATCH-1]); */
                s.ins_h = zlib_deflate.HASH(s, s.ins_h, s.window[str + zlib_Pako_trees.MIN_MATCH - 1]);

                s.prev[str & s.w_mask] = (<Uint16Array>s.head)[s.ins_h];

                (<Uint16Array>s.head)[s.ins_h] = str;
                str++;
            } while (--n);
            s.strstart = str;
            s.lookahead = zlib_Pako_trees.MIN_MATCH - 1;
            zlib_deflate.fill_window(s);
        }
        s.strstart += s.lookahead;
        s.block_start = s.strstart;
        s.insert = s.lookahead;
        s.lookahead = 0;
        s.match_length = s.prev_length = zlib_Pako_trees.MIN_MATCH - 1;
        s.match_available = 0;
        strm.next_in = next;
        strm.input = input;
        strm.avail_in = avail;
        s.wrap = wrap;
        return zlib_constants.Z_OK;
    }
}

//module.exports.deflateInit = deflateInit;
//module.exports.deflateInit2 = deflateInit2;
//module.exports.deflateReset = deflateReset;
//module.exports.deflateResetKeep = deflateResetKeep;
//module.exports.deflateSetHeader = deflateSetHeader;
//module.exports.deflate = deflate;
//module.exports.deflateEnd = deflateEnd;
//module.exports.deflateSetDictionary = deflateSetDictionary;
//module.exports.deflateInfo = 'pako deflate (from Nodeca project)';

/* Not implemented
module.exports.deflateBound = deflateBound;
module.exports.deflateCopy = deflateCopy;
module.exports.deflateParams = deflateParams;
module.exports.deflatePending = deflatePending;
module.exports.deflatePrime = deflatePrime;
module.exports.deflateTune = deflateTune;
*/
