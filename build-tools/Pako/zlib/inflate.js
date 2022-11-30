'use strict';
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.zlib_inflate = exports.zlib_InflateState = void 0;
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
const ZLIB = __importStar(require("./zlibfull.js"));
class zlib_InflateState {
    constructor() {
        this.mode = 0; /* current inflate mode */
        this.last = 0; /* true if processing last block */
        this.wrap = 0; /* bit 0 true for zlib, bit 1 true for gzip */
        this.havedict = 0; /* true if dictionary provided */
        this.flags = 0; /* gzip header method and flags (0 if zlib) */
        this.dmax = 0; /* zlib header max distance (INFLATE_STRICT) */
        this.check = 0; /* protected copy of check value */
        this.total = 0; /* protected copy of output count */
        // TODO: may be {}
        this.head = null; /* where to save gzip header information */
        /* sliding window */
        this.wbits = 0; /* log base 2 of requested window size */
        this.wsize = 0; /* window size or zero if not using window */
        this.whave = 0; /* valid bytes in the window */
        this.wnext = 0; /* window write index */
        this.window = null; /* allocated sliding window, if needed */
        /* bit accumulator */
        this.hold = 0; /* input bit accumulator */
        this.bits = 0; /* number of bits in "in" */
        /* for string and stored block copying */
        this.length = 0; /* literal or length of data to copy */
        this.offset = 0; /* distance back to copy string from */
        /* for table and code decoding */
        this.extra = 0; /* extra bits needed */
        /* fixed and dynamic code tables */
        this.lencode = null; /* starting table for length/literal codes */
        this.distcode = null; /* starting table for distance codes */
        this.lenbits = 0; /* index bits for lencode */
        this.distbits = 0; /* index bits for distcode */
        /* dynamic table building */
        this.ncode = 0; /* number of code length code lengths */
        this.nlen = 0; /* number of length code lengths */
        this.ndist = 0; /* number of distance code lengths */
        this.have = 0; /* number of code lengths in lens[] */
        this.next = null; /* next available space in codes[] */
        this.lens = new Uint16Array(320); /* temporary storage for code lengths */
        this.work = new Uint16Array(288); /* work area for code table building */
        /*
         because we don't have pointers in js, we use lencode and distcode directly
         as buffers so we don't need codes
        */
        //this.codes = new Int32Array(ENOUGH);       /* space for code tables */
        this.lendyn = null; /* dynamic table for length/literal codes (JS specific) */
        this.distdyn = null; /* dynamic table for distance codes (JS specific) */
        this.sane = 0; /* if false, allow invalid distance too far */
        this.back = 0; /* bits back of last unprocessed length/lit */
        this.was = 0; /* initial length of match */
    }
}
exports.zlib_InflateState = zlib_InflateState;
class zlib_inflate {
    static zswap32(q) {
        return (((q >>> 24) & 0xff) +
            ((q >>> 8) & 0xff00) +
            ((q & 0xff00) << 8) +
            ((q & 0xff) << 24));
    }
    static inflateResetKeep(strm) {
        if (!strm || !strm.state) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        let state = strm.state;
        strm.total_in = strm.total_out = state.total = 0;
        strm.msg = ''; /*Z_NULL*/
        if (state.wrap) { /* to support ill-conceived Java test suite */
            strm.adler = state.wrap & 1;
        }
        state.mode = zlib_inflate.HEAD;
        state.last = 0;
        state.havedict = 0;
        state.dmax = 32768;
        state.head = null /*Z_NULL*/;
        state.hold = 0;
        state.bits = 0;
        //state.lencode = state.distcode = state.next = state.codes;
        state.lencode = state.lendyn = new Int32Array(zlib_inflate.ENOUGH_LENS);
        state.distcode = state.distdyn = new Int32Array(zlib_inflate.ENOUGH_DISTS);
        state.sane = 1;
        state.back = -1;
        //Tracev((stderr, "inflate: reset\n"));
        return ZLIB.zlib_constants.Z_OK;
    }
    static inflateReset(strm) {
        if (!strm || !strm.state) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        const state = strm.state;
        state.wsize = 0;
        state.whave = 0;
        state.wnext = 0;
        return zlib_inflate.inflateResetKeep(strm);
    }
    ;
    static inflateReset2(strm, windowBits) {
        let wrap;
        /* get the state */
        if (!strm || !strm.state) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        let state = strm.state;
        /* extract wrap request from windowBits parameter */
        if (windowBits < 0) {
            wrap = 0;
            windowBits = -windowBits;
        }
        else {
            wrap = (windowBits >> 4) + 1;
            if (windowBits < 48) {
                windowBits &= 15;
            }
        }
        /* set number of window bits, free window if different */
        if (windowBits && (windowBits < 8 || windowBits > 15)) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        if (state.window !== null && state.wbits !== windowBits) {
            state.window = null;
        }
        /* update state and reset the rest of it */
        state.wrap = wrap;
        state.wbits = windowBits;
        return zlib_inflate.inflateReset(strm);
    }
    ;
    static inflateInit2(strm, windowBits) {
        if (!strm) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        //strm.msg = ZLIB.Pako_constants.Z_NULL;                 /* in case we return an error */
        let state = new zlib_InflateState();
        //if (state === ZLIB.Pako_constants.Z_NULL) return ZLIB.Pako_constants.Z_MEM_ERROR;
        //Tracev((stderr, "inflate: allocated\n"));
        strm.state = state;
        state.window = null /*Z_NULL*/;
        const ret = zlib_inflate.inflateReset2(strm, windowBits);
        if (ret !== ZLIB.zlib_constants.Z_OK) {
            strm.state = null /*Z_NULL*/;
        }
        return ret;
    }
    ;
    static inflateInit(strm) {
        return zlib_inflate.inflateInit2(strm, zlib_inflate.DEF_WBITS);
    }
    static fixedtables(state) {
        /* build fixed huffman tables if first call (may not be thread safe) */
        if (zlib_inflate.virgin) {
            zlib_inflate.lenfix = new Int32Array(512);
            zlib_inflate.distfix = new Int32Array(32);
            /* literal/length table */
            let sym = 0;
            while (sym < 144) {
                state.lens[sym++] = 8;
            }
            while (sym < 256) {
                state.lens[sym++] = 9;
            }
            while (sym < 280) {
                state.lens[sym++] = 7;
            }
            while (sym < 288) {
                state.lens[sym++] = 8;
            }
            ZLIB.zlib_inftrees.inflate_table(zlib_inflate.LENS, state.lens, 0, 288, zlib_inflate.lenfix, 0, state.work, { bits: 9 });
            /* distance table */
            sym = 0;
            while (sym < 32) {
                state.lens[sym++] = 5;
            }
            ZLIB.zlib_inftrees.inflate_table(zlib_inflate.DISTS, state.lens, 0, 32, zlib_inflate.distfix, 0, state.work, { bits: 5 });
            /* do this just once */
            zlib_inflate.virgin = false;
        }
        state.lencode = zlib_inflate.lenfix;
        state.lenbits = 9;
        state.distcode = zlib_inflate.distfix;
        state.distbits = 5;
    }
    ;
    /*
     Update the window with the last wsize (normally 32K) bytes written before
     returning.  If window does not exist yet, create it.  This is only called
     when a window is already in use, or when output has been written during this
     inflate call, but the end of the deflate stream has not been reached yet.
     It is also called to create a window for dictionary data when a dictionary
     is loaded.

     Providing output buffers larger than 32K to inflate() should provide a speed
     advantage, since only the last 32K of output is copied to the sliding window
     upon return from inflate(), and since all distances after the first 32K of
     output will fall in the output data, making match copies simpler and faster.
     The advantage may be dependent on the size of the processor's data caches.
     */
    static updatewindow(strm, src, end, copy) {
        let dist;
        const state = strm.state;
        /* if it hasn't been done already, allocate space for the window */
        if (state.window === null) {
            state.wsize = 1 << state.wbits;
            state.wnext = 0;
            state.whave = 0;
            state.window = new Uint8Array(state.wsize);
        }
        /* copy state->wsize or less output bytes into the circular window */
        if (copy >= state.wsize) {
            state.window.set(src.subarray(end - state.wsize, end), 0);
            state.wnext = 0;
            state.whave = state.wsize;
        }
        else {
            dist = state.wsize - state.wnext;
            if (dist > copy) {
                dist = copy;
            }
            //zmemcpy(state->window + state->wnext, end - copy, dist);
            state.window.set(src.subarray(end - copy, end - copy + dist), state.wnext);
            copy -= dist;
            if (copy) {
                //zmemcpy(state->window, end - copy, copy);
                state.window.set(src.subarray(end - copy, end), 0);
                state.wnext = copy;
                state.whave = state.wsize;
            }
            else {
                state.wnext += dist;
                if (state.wnext === state.wsize) {
                    state.wnext = 0;
                }
                if (state.whave < state.wsize) {
                    state.whave += dist;
                }
            }
        }
        return 0;
    }
    ;
    static inflate(strm, flush) {
        let state;
        let input, output; // input/output buffers
        let next; /* next input INDEX */
        let put; /* next output INDEX */
        let have, left; /* available input and output */
        let hold; /* bit buffer */
        let bits; /* bits in bit buffer */
        let _in;
        let _out; /* save starting available input and output */
        let copy; /* number of stored or match bytes to copy */
        let from; /* where to copy match bytes from */
        let from_source;
        let here = 0; /* current decoding table entry */
        let here_bits;
        let here_op;
        let here_val; // paked "here" denormalized (JS specific)
        //let last;                   /* parent table entry */
        let last_bits;
        let last_op;
        let last_val; // paked "last" denormalized (JS specific)
        let len; /* length to copy for repeats, bits to drop */
        let ret; /* return code */
        const hbuf = new Uint8Array(4); /* buffer for gzip header crc calculation */
        let opts;
        let n; // temporary variable for NEED_BITS
        let order = /* permutation of code lengths */ new Uint8Array([16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15]);
        if (!strm || !strm.state || !strm.output ||
            (!strm.input && strm.avail_in !== 0)) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        state = strm.state;
        if (state.mode === zlib_inflate.TYPE) {
            state.mode = zlib_inflate.TYPEDO;
        } /* skip check */
        //--- LOAD() ---
        put = strm.next_out;
        output = strm.output;
        left = strm.avail_out;
        next = strm.next_in;
        input = strm.input;
        have = strm.avail_in;
        hold = state.hold;
        bits = state.bits;
        //---
        _in = have;
        _out = left;
        ret = ZLIB.zlib_constants.Z_OK;
        inf_leave: // goto emulation
         for (;;) {
            switch (state.mode) {
                case zlib_inflate.HEAD:
                    if (state.wrap === 0) {
                        state.mode = zlib_inflate.TYPEDO;
                        break;
                    }
                    //=== NEEDBITS(16);
                    while (bits < 16) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                    }
                    //===//
                    if ((state.wrap & 2) && hold === 0x8b1f) { /* gzip header */
                        state.check = 0 /*crc32(0L, ZLIB.Pako_constants.Z_NULL, 0)*/;
                        //=== CRC2(state.check, hold);
                        hbuf[0] = hold & 0xff;
                        hbuf[1] = (hold >>> 8) & 0xff;
                        state.check = ZLIB.zlib_crc32.crc32(state.check, hbuf, 2, 0);
                        //===//
                        //=== INITBITS();
                        hold = 0;
                        bits = 0;
                        //===//
                        state.mode = zlib_inflate.FLAGS;
                        break;
                    }
                    state.flags = 0; /* expect zlib header */
                    if (state.head) {
                        state.head.done = false;
                    }
                    if (!(state.wrap & 1) || /* check if zlib header allowed */
                        (((hold & 0xff) /*BITS(8)*/ << 8) + (hold >> 8)) % 31) {
                        strm.msg = 'incorrect header check';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    if ((hold & 0x0f) /*BITS(4)*/ !== ZLIB.zlib_constants.Z_DEFLATED) {
                        strm.msg = 'unknown compression method';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    //--- DROPBITS(4) ---//
                    hold >>>= 4;
                    bits -= 4;
                    //---//
                    len = (hold & 0x0f) /*BITS(4)*/ + 8;
                    if (state.wbits === 0) {
                        state.wbits = len;
                    }
                    else if (len > state.wbits) {
                        strm.msg = 'invalid window size';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    // !!! pako patch. Force use `options.windowBits` if passed.
                    // Required to always use max window size by default.
                    state.dmax = 1 << state.wbits;
                    //state.dmax = 1 << len;
                    //Tracev((stderr, "inflate:   zlib header ok\n"));
                    strm.adler = state.check = 1 /*adler32(0L, ZLIB.Pako_constants.Z_NULL, 0)*/;
                    state.mode = hold & 0x200 ? zlib_inflate.DICTID : zlib_inflate.TYPE;
                    //=== INITBITS();
                    hold = 0;
                    bits = 0;
                    //===//
                    break;
                case zlib_inflate.FLAGS:
                    //=== NEEDBITS(16); */
                    while (bits < 16) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                    }
                    //===//
                    state.flags = hold;
                    if ((state.flags & 0xff) !== ZLIB.zlib_constants.Z_DEFLATED) {
                        strm.msg = 'unknown compression method';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    if (state.flags & 0xe000) {
                        strm.msg = 'unknown header flags set';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    if (state.head) {
                        state.head.text = ((hold >> 8) & 1);
                    }
                    if (state.flags & 0x0200) {
                        //=== CRC2(state.check, hold);
                        hbuf[0] = hold & 0xff;
                        hbuf[1] = (hold >>> 8) & 0xff;
                        state.check = ZLIB.zlib_crc32.crc32(state.check, hbuf, 2, 0);
                        //===//
                    }
                    //=== INITBITS();
                    hold = 0;
                    bits = 0;
                    //===//
                    state.mode = zlib_inflate.TIME;
                /* falls through */
                case zlib_inflate.TIME:
                    //=== NEEDBITS(32); */
                    while (bits < 32) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                    }
                    //===//
                    if (state.head) {
                        state.head.time = hold;
                    }
                    if (state.flags & 0x0200) {
                        //=== CRC4(state.check, hold)
                        hbuf[0] = hold & 0xff;
                        hbuf[1] = (hold >>> 8) & 0xff;
                        hbuf[2] = (hold >>> 16) & 0xff;
                        hbuf[3] = (hold >>> 24) & 0xff;
                        state.check = ZLIB.zlib_crc32.crc32(state.check, hbuf, 4, 0);
                        //===
                    }
                    //=== INITBITS();
                    hold = 0;
                    bits = 0;
                    //===//
                    state.mode = zlib_inflate.OS;
                /* falls through */
                case zlib_inflate.OS:
                    //=== NEEDBITS(16); */
                    while (bits < 16) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                    }
                    //===//
                    if (state.head) {
                        state.head.xflags = (hold & 0xff);
                        state.head.os = (hold >> 8);
                    }
                    if (state.flags & 0x0200) {
                        //=== CRC2(state.check, hold);
                        hbuf[0] = hold & 0xff;
                        hbuf[1] = (hold >>> 8) & 0xff;
                        state.check = ZLIB.zlib_crc32.crc32(state.check, hbuf, 2, 0);
                        //===//
                    }
                    //=== INITBITS();
                    hold = 0;
                    bits = 0;
                    //===//
                    state.mode = zlib_inflate.EXLEN;
                /* falls through */
                case zlib_inflate.EXLEN:
                    if (state.flags & 0x0400) {
                        //=== NEEDBITS(16); */
                        while (bits < 16) {
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                        }
                        //===//
                        state.length = hold;
                        if (state.head) {
                            state.head.extra_len = hold;
                        }
                        if (state.flags & 0x0200) {
                            //=== CRC2(state.check, hold);
                            hbuf[0] = hold & 0xff;
                            hbuf[1] = (hold >>> 8) & 0xff;
                            state.check = ZLIB.zlib_crc32.crc32(state.check, hbuf, 2, 0);
                            //===//
                        }
                        //=== INITBITS();
                        hold = 0;
                        bits = 0;
                        //===//
                    }
                    else if (state.head) {
                        state.head.extra = null /*Z_NULL*/;
                    }
                    state.mode = zlib_inflate.EXTRA;
                /* falls through */
                case zlib_inflate.EXTRA:
                    if (state.flags & 0x0400) {
                        copy = state.length;
                        if (copy > have) {
                            copy = have;
                        }
                        if (copy) {
                            if (state.head) {
                                len = state.head.extra_len - state.length;
                                if (!state.head.extra) {
                                    // Use untyped array for more convenient processing later
                                    state.head.extra = new Uint8Array(state.head.extra_len);
                                }
                                state.head.extra.set(input.subarray(next, 
                                // extra field is limited to 65536 bytes
                                // - no need for additional size check
                                next + copy), 
                                /*len + copy > state.head.extra_max - len ? state.head.extra_max : copy,*/
                                len);
                                //zmemcpy(state.head.extra + len, next,
                                //        len + copy > state.head.extra_max ?
                                //        state.head.extra_max - len : copy);
                            }
                            if (state.flags & 0x0200) {
                                state.check = ZLIB.zlib_crc32.crc32(state.check, input, copy, next);
                            }
                            have -= copy;
                            next += copy;
                            state.length -= copy;
                        }
                        if (state.length) {
                            break inf_leave;
                        }
                    }
                    state.length = 0;
                    state.mode = zlib_inflate.NAME;
                /* falls through */
                case zlib_inflate.NAME:
                    if (state.flags & 0x0800) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        copy = 0;
                        do {
                            // TODO: 2 or 1 bytes?
                            len = input[next + copy++];
                            /* use constant limit because in js we should not preallocate memory */
                            if (state.head && len &&
                                (state.length < 65536 /*state.head.name_max*/)) {
                                state.head.name += String.fromCharCode(len);
                            }
                        } while (len && copy < have);
                        if (state.flags & 0x0200) {
                            state.check = ZLIB.zlib_crc32.crc32(state.check, input, copy, next);
                        }
                        have -= copy;
                        next += copy;
                        if (len) {
                            break inf_leave;
                        }
                    }
                    else if (state.head) {
                        state.head.name = null;
                    }
                    state.length = 0;
                    state.mode = zlib_inflate.COMMENT;
                /* falls through */
                case zlib_inflate.COMMENT:
                    if (state.flags & 0x1000) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        copy = 0;
                        do {
                            len = input[next + copy++];
                            /* use constant limit because in js we should not preallocate memory */
                            if (state.head && len &&
                                (state.length < 65536 /*state.head.comm_max*/)) {
                                state.head.comment += String.fromCharCode(len);
                            }
                        } while (len && copy < have);
                        if (state.flags & 0x0200) {
                            state.check = ZLIB.zlib_crc32.crc32(state.check, input, copy, next);
                        }
                        have -= copy;
                        next += copy;
                        if (len) {
                            break inf_leave;
                        }
                    }
                    else if (state.head) {
                        state.head.comment = null;
                    }
                    state.mode = zlib_inflate.HCRC;
                /* falls through */
                case zlib_inflate.HCRC:
                    if (state.flags & 0x0200) {
                        //=== NEEDBITS(16); */
                        while (bits < 16) {
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                        }
                        //===//
                        if (hold !== (state.check & 0xffff)) {
                            strm.msg = 'header crc mismatch';
                            state.mode = zlib_inflate.BAD;
                            break;
                        }
                        //=== INITBITS();
                        hold = 0;
                        bits = 0;
                        //===//
                    }
                    if (state.head) {
                        state.head.hcrc = ((state.flags >> 9) & 1);
                        state.head.done = true;
                    }
                    strm.adler = state.check = 0;
                    state.mode = zlib_inflate.TYPE;
                    break;
                case zlib_inflate.DICTID:
                    //=== NEEDBITS(32); */
                    while (bits < 32) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                    }
                    //===//
                    strm.adler = state.check = zlib_inflate.zswap32(hold);
                    //=== INITBITS();
                    hold = 0;
                    bits = 0;
                    //===//
                    state.mode = zlib_inflate.DICT;
                /* falls through */
                case zlib_inflate.DICT:
                    if (!state.havedict) {
                        //--- RESTORE() ---
                        strm.next_out = put;
                        strm.avail_out = left;
                        strm.next_in = next;
                        strm.avail_in = have;
                        state.hold = hold;
                        state.bits = bits;
                        //---
                        return ZLIB.zlib_constants.Z_NEED_DICT;
                    }
                    strm.adler = state.check = 1 /*adler32(0L, ZLIB.Pako_constants.Z_NULL, 0)*/;
                    state.mode = zlib_inflate.TYPE;
                /* falls through */
                case zlib_inflate.TYPE:
                    if (flush === ZLIB.zlib_constants.Z_BLOCK || flush === ZLIB.zlib_constants.Z_TREES) {
                        break inf_leave;
                    }
                /* falls through */
                case zlib_inflate.TYPEDO:
                    if (state.last) {
                        //--- BYTEBITS() ---//
                        hold >>>= bits & 7;
                        bits -= bits & 7;
                        //---//
                        state.mode = zlib_inflate.CHECK;
                        break;
                    }
                    //=== NEEDBITS(3); */
                    while (bits < 3) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                    }
                    //===//
                    state.last = (hold & 0x01) /*BITS(1)*/;
                    //--- DROPBITS(1) ---//
                    hold >>>= 1;
                    bits -= 1;
                    //---//
                    switch ((hold & 0x03) /*BITS(2)*/) {
                        case 0: /* stored block */
                            //Tracev((stderr, "inflate:     stored block%s\n",
                            //        state.last ? " (last)" : ""));
                            state.mode = zlib_inflate.STORED;
                            break;
                        case 1: /* fixed block */
                            zlib_inflate.fixedtables(state);
                            //Tracev((stderr, "inflate:     fixed codes block%s\n",
                            //        state.last ? " (last)" : ""));
                            state.mode = zlib_inflate.LEN_; /* decode codes */
                            if (flush === ZLIB.zlib_constants.Z_TREES) {
                                //--- DROPBITS(2) ---//
                                hold >>>= 2;
                                bits -= 2;
                                //---//
                                break inf_leave;
                            }
                            break;
                        case 2: /* dynamic block */
                            //Tracev((stderr, "inflate:     dynamic codes block%s\n",
                            //        state.last ? " (last)" : ""));
                            state.mode = zlib_inflate.TABLE;
                            break;
                        case 3:
                            strm.msg = 'invalid block type';
                            state.mode = zlib_inflate.BAD;
                    }
                    //--- DROPBITS(2) ---//
                    hold >>>= 2;
                    bits -= 2;
                    //---//
                    break;
                case zlib_inflate.STORED:
                    //--- BYTEBITS() ---// /* go to byte boundary */
                    hold >>>= bits & 7;
                    bits -= bits & 7;
                    //---//
                    //=== NEEDBITS(32); */
                    while (bits < 32) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                    }
                    //===//
                    if ((hold & 0xffff) !== ((hold >>> 16) ^ 0xffff)) {
                        strm.msg = 'invalid stored block lengths';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    state.length = hold & 0xffff;
                    //Tracev((stderr, "inflate:       stored length %u\n",
                    //        state.length));
                    //=== INITBITS();
                    hold = 0;
                    bits = 0;
                    //===//
                    state.mode = zlib_inflate.COPY_;
                    if (flush === ZLIB.zlib_constants.Z_TREES) {
                        break inf_leave;
                    }
                /* falls through */
                case zlib_inflate.COPY_:
                    state.mode = zlib_inflate.COPY;
                /* falls through */
                case zlib_inflate.COPY:
                    copy = state.length;
                    if (copy) {
                        if (copy > have) {
                            copy = have;
                        }
                        if (copy > left) {
                            copy = left;
                        }
                        if (copy === 0) {
                            break inf_leave;
                        }
                        //--- zmemcpy(put, next, copy); ---
                        output.set(input.subarray(next, next + copy), put);
                        //---//
                        have -= copy;
                        next += copy;
                        left -= copy;
                        put += copy;
                        state.length -= copy;
                        break;
                    }
                    //Tracev((stderr, "inflate:       stored end\n"));
                    state.mode = zlib_inflate.TYPE;
                    break;
                case zlib_inflate.TABLE:
                    //=== NEEDBITS(14); */
                    while (bits < 14) {
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                    }
                    //===//
                    state.nlen = (hold & 0x1f) /*BITS(5)*/ + 257;
                    //--- DROPBITS(5) ---//
                    hold >>>= 5;
                    bits -= 5;
                    //---//
                    state.ndist = (hold & 0x1f) /*BITS(5)*/ + 1;
                    //--- DROPBITS(5) ---//
                    hold >>>= 5;
                    bits -= 5;
                    //---//
                    state.ncode = (hold & 0x0f) /*BITS(4)*/ + 4;
                    //--- DROPBITS(4) ---//
                    hold >>>= 4;
                    bits -= 4;
                    //---//
                    //#ifndef PKZIP_BUG_WORKAROUND
                    if (state.nlen > 286 || state.ndist > 30) {
                        strm.msg = 'too many length or distance symbols';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    //#endif
                    //Tracev((stderr, "inflate:       table sizes ok\n"));
                    state.have = 0;
                    state.mode = zlib_inflate.LENLENS;
                /* falls through */
                case zlib_inflate.LENLENS:
                    while (state.have < state.ncode) {
                        //=== NEEDBITS(3);
                        while (bits < 3) {
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                        }
                        //===//
                        state.lens[order[state.have++]] = (hold & 0x07); //BITS(3);
                        //--- DROPBITS(3) ---//
                        hold >>>= 3;
                        bits -= 3;
                        //---//
                    }
                    while (state.have < 19) {
                        state.lens[order[state.have++]] = 0;
                    }
                    // We have separate tables & no pointers. 2 commented lines below not needed.
                    //state.next = state.codes;
                    //state.lencode = state.next;
                    // Switch to use dynamic table
                    state.lencode = state.lendyn;
                    state.lenbits = 7;
                    opts = { bits: state.lenbits };
                    ret = ZLIB.zlib_inftrees.inflate_table(zlib_inflate.CODES, state.lens, 0, 19, state.lencode, 0, state.work, opts);
                    state.lenbits = opts.bits;
                    if (ret) {
                        strm.msg = 'invalid code lengths set';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    //Tracev((stderr, "inflate:       code lengths ok\n"));
                    state.have = 0;
                    state.mode = zlib_inflate.CODELENS;
                /* falls through */
                case zlib_inflate.CODELENS:
                    while (state.have < state.nlen + state.ndist) {
                        for (;;) {
                            here = state.lencode[hold & ((1 << state.lenbits) - 1)]; /*BITS(state.lenbits)*/
                            here_bits = here >>> 24;
                            here_op = (here >>> 16) & 0xff;
                            here_val = here & 0xffff;
                            if ((here_bits) <= bits) {
                                break;
                            }
                            //--- PULLBYTE() ---//
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                            //---//
                        }
                        if (here_val < 16) {
                            //--- DROPBITS(here.bits) ---//
                            hold >>>= here_bits;
                            bits -= here_bits;
                            //---//
                            state.lens[state.have++] = here_val;
                        }
                        else {
                            if (here_val === 16) {
                                //=== NEEDBITS(here.bits + 2);
                                n = here_bits + 2;
                                while (bits < n) {
                                    if (have === 0) {
                                        break inf_leave;
                                    }
                                    have--;
                                    hold += input[next++] << bits;
                                    bits += 8;
                                }
                                //===//
                                //--- DROPBITS(here.bits) ---//
                                hold >>>= here_bits;
                                bits -= here_bits;
                                //---//
                                if (state.have === 0) {
                                    strm.msg = 'invalid bit length repeat';
                                    state.mode = zlib_inflate.BAD;
                                    break;
                                }
                                len = state.lens[state.have - 1];
                                copy = 3 + (hold & 0x03); //BITS(2);
                                //--- DROPBITS(2) ---//
                                hold >>>= 2;
                                bits -= 2;
                                //---//
                            }
                            else if (here_val === 17) {
                                //=== NEEDBITS(here.bits + 3);
                                n = here_bits + 3;
                                while (bits < n) {
                                    if (have === 0) {
                                        break inf_leave;
                                    }
                                    have--;
                                    hold += input[next++] << bits;
                                    bits += 8;
                                }
                                //===//
                                //--- DROPBITS(here.bits) ---//
                                hold >>>= here_bits;
                                bits -= here_bits;
                                //---//
                                len = 0;
                                copy = 3 + (hold & 0x07); //BITS(3);
                                //--- DROPBITS(3) ---//
                                hold >>>= 3;
                                bits -= 3;
                                //---//
                            }
                            else {
                                //=== NEEDBITS(here.bits + 7);
                                n = here_bits + 7;
                                while (bits < n) {
                                    if (have === 0) {
                                        break inf_leave;
                                    }
                                    have--;
                                    hold += input[next++] << bits;
                                    bits += 8;
                                }
                                //===//
                                //--- DROPBITS(here.bits) ---//
                                hold >>>= here_bits;
                                bits -= here_bits;
                                //---//
                                len = 0;
                                copy = 11 + (hold & 0x7f); //BITS(7);
                                //--- DROPBITS(7) ---//
                                hold >>>= 7;
                                bits -= 7;
                                //---//
                            }
                            if (state.have + copy > state.nlen + state.ndist) {
                                strm.msg = 'invalid bit length repeat';
                                state.mode = zlib_inflate.BAD;
                                break;
                            }
                            while (copy--) {
                                state.lens[state.have++] = len;
                            }
                        }
                    }
                    /* handle error breaks in while */
                    if (state.mode === zlib_inflate.BAD) {
                        break;
                    }
                    /* check for end-of-block code (better have one) */
                    if (state.lens[256] === 0) {
                        strm.msg = 'invalid code -- missing end-of-block';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    /* build code tables -- note: do not change the lenbits or distbits
                       values here (9 and 6) without reading the comments in inftrees.h
                       concerning the ENOUGH constants, which depend on those values */
                    state.lenbits = 9;
                    opts = { bits: state.lenbits };
                    ret = ZLIB.zlib_inftrees.inflate_table(zlib_inflate.LENS, state.lens, 0, state.nlen, state.lencode, 0, state.work, opts);
                    // We have separate tables & no pointers. 2 commented lines below not needed.
                    // state.next_index = opts.table_index;
                    state.lenbits = opts.bits;
                    // state.lencode = state.next;
                    if (ret) {
                        strm.msg = 'invalid literal/lengths set';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    state.distbits = 6;
                    //state.distcode.copy(state.codes);
                    // Switch to use dynamic table
                    state.distcode = state.distdyn;
                    opts = { bits: state.distbits };
                    ret = ZLIB.zlib_inftrees.inflate_table(zlib_inflate.DISTS, state.lens, state.nlen, state.ndist, state.distcode, 0, state.work, opts);
                    // We have separate tables & no pointers. 2 commented lines below not needed.
                    // state.next_index = opts.table_index;
                    state.distbits = opts.bits;
                    // state.distcode = state.next;
                    if (ret) {
                        strm.msg = 'invalid distances set';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    //Tracev((stderr, 'inflate:       codes ok\n'));
                    state.mode = zlib_inflate.LEN_;
                    if (flush === ZLIB.zlib_constants.Z_TREES) {
                        break inf_leave;
                    }
                /* falls through */
                case zlib_inflate.LEN_:
                    state.mode = zlib_inflate.LEN;
                /* falls through */
                case zlib_inflate.LEN:
                    if (have >= 6 && left >= 258) {
                        //--- RESTORE() ---
                        strm.next_out = put;
                        strm.avail_out = left;
                        strm.next_in = next;
                        strm.avail_in = have;
                        state.hold = hold;
                        state.bits = bits;
                        //---
                        ZLIB.zlib_inffast.inflate_fast(strm, _out);
                        //--- LOAD() ---
                        put = strm.next_out;
                        output = strm.output;
                        left = strm.avail_out;
                        next = strm.next_in;
                        input = strm.input;
                        have = strm.avail_in;
                        hold = state.hold;
                        bits = state.bits;
                        //---
                        if (state.mode === zlib_inflate.TYPE) {
                            state.back = -1;
                        }
                        break;
                    }
                    state.back = 0;
                    for (;;) {
                        here = state.lencode[hold & ((1 << state.lenbits) - 1)]; /*BITS(state.lenbits)*/
                        here_bits = here >>> 24;
                        here_op = (here >>> 16) & 0xff;
                        here_val = here & 0xffff;
                        if (here_bits <= bits) {
                            break;
                        }
                        //--- PULLBYTE() ---//
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                        //---//
                    }
                    if (here_op && (here_op & 0xf0) === 0) {
                        last_bits = here_bits;
                        last_op = here_op;
                        last_val = here_val;
                        for (;;) {
                            here = state.lencode[last_val +
                                ((hold & ((1 << (last_bits + last_op)) - 1)) /*BITS(last.bits + last.op)*/ >> last_bits)];
                            here_bits = here >>> 24;
                            here_op = (here >>> 16) & 0xff;
                            here_val = here & 0xffff;
                            if ((last_bits + here_bits) <= bits) {
                                break;
                            }
                            //--- PULLBYTE() ---//
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                            //---//
                        }
                        //--- DROPBITS(last.bits) ---//
                        hold >>>= last_bits;
                        bits -= last_bits;
                        //---//
                        state.back += last_bits;
                    }
                    //--- DROPBITS(here.bits) ---//
                    hold >>>= here_bits;
                    bits -= here_bits;
                    //---//
                    state.back += here_bits;
                    state.length = here_val;
                    if (here_op === 0) {
                        //Tracevv((stderr, here.val >= 0x20 && here.val < 0x7f ?
                        //        "inflate:         literal '%c'\n" :
                        //        "inflate:         literal 0x%02x\n", here.val));
                        state.mode = zlib_inflate.LIT;
                        break;
                    }
                    if (here_op & 32) {
                        //Tracevv((stderr, "inflate:         end of block\n"));
                        state.back = -1;
                        state.mode = zlib_inflate.TYPE;
                        break;
                    }
                    if (here_op & 64) {
                        strm.msg = 'invalid literal/length code';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    state.extra = here_op & 15;
                    state.mode = zlib_inflate.LENEXT;
                /* falls through */
                case zlib_inflate.LENEXT:
                    if (state.extra) {
                        //=== NEEDBITS(state.extra);
                        n = state.extra;
                        while (bits < n) {
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                        }
                        //===//
                        state.length += hold & ((1 << state.extra) - 1) /*BITS(state.extra)*/;
                        //--- DROPBITS(state.extra) ---//
                        hold >>>= state.extra;
                        bits -= state.extra;
                        //---//
                        state.back += state.extra;
                    }
                    //Tracevv((stderr, "inflate:         length %u\n", state.length));
                    state.was = state.length;
                    state.mode = zlib_inflate.DIST;
                /* falls through */
                case zlib_inflate.DIST:
                    for (;;) {
                        here = state.distcode[hold & ((1 << state.distbits) - 1)]; /*BITS(state.distbits)*/
                        here_bits = here >>> 24;
                        here_op = (here >>> 16) & 0xff;
                        here_val = here & 0xffff;
                        if ((here_bits) <= bits) {
                            break;
                        }
                        //--- PULLBYTE() ---//
                        if (have === 0) {
                            break inf_leave;
                        }
                        have--;
                        hold += input[next++] << bits;
                        bits += 8;
                        //---//
                    }
                    if ((here_op & 0xf0) === 0) {
                        last_bits = here_bits;
                        last_op = here_op;
                        last_val = here_val;
                        for (;;) {
                            here = state.distcode[last_val +
                                ((hold & ((1 << (last_bits + last_op)) - 1)) /*BITS(last.bits + last.op)*/ >> last_bits)];
                            here_bits = here >>> 24;
                            here_op = (here >>> 16) & 0xff;
                            here_val = here & 0xffff;
                            if ((last_bits + here_bits) <= bits) {
                                break;
                            }
                            //--- PULLBYTE() ---//
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                            //---//
                        }
                        //--- DROPBITS(last.bits) ---//
                        hold >>>= last_bits;
                        bits -= last_bits;
                        //---//
                        state.back += last_bits;
                    }
                    //--- DROPBITS(here.bits) ---//
                    hold >>>= here_bits;
                    bits -= here_bits;
                    //---//
                    state.back += here_bits;
                    if (here_op & 64) {
                        strm.msg = 'invalid distance code';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    state.offset = here_val;
                    state.extra = (here_op) & 15;
                    state.mode = zlib_inflate.DISTEXT;
                /* falls through */
                case zlib_inflate.DISTEXT:
                    if (state.extra) {
                        //=== NEEDBITS(state.extra);
                        n = state.extra;
                        while (bits < n) {
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                        }
                        //===//
                        state.offset += hold & ((1 << state.extra) - 1) /*BITS(state.extra)*/;
                        //--- DROPBITS(state.extra) ---//
                        hold >>>= state.extra;
                        bits -= state.extra;
                        //---//
                        state.back += state.extra;
                    }
                    //#ifdef INFLATE_STRICT
                    if (state.offset > state.dmax) {
                        strm.msg = 'invalid distance too far back';
                        state.mode = zlib_inflate.BAD;
                        break;
                    }
                    //#endif
                    //Tracevv((stderr, "inflate:         distance %u\n", state.offset));
                    state.mode = zlib_inflate.MATCH;
                /* falls through */
                case zlib_inflate.MATCH:
                    if (left === 0) {
                        break inf_leave;
                    }
                    copy = _out - left;
                    if (state.offset > copy) { /* copy from window */
                        copy = state.offset - copy;
                        if (copy > state.whave) {
                            if (state.sane) {
                                strm.msg = 'invalid distance too far back';
                                state.mode = zlib_inflate.BAD;
                                break;
                            }
                            // (!) This block is disabled in zlib defaults,
                            // don't enable it for binary compatibility
                            //#ifdef INFLATE_ALLOW_INVALID_DISTANCE_TOOFAR_ARRR
                            //          Trace((stderr, "inflate.c too far\n"));
                            //          copy -= state.whave;
                            //          if (copy > state.length) { copy = state.length; }
                            //          if (copy > left) { copy = left; }
                            //          left -= copy;
                            //          state.length -= copy;
                            //          do {
                            //            output[put++] = 0;
                            //          } while (--copy);
                            //          if (state.length === 0) { state.mode = LEN; }
                            //          break;
                            //#endif
                        }
                        if (copy > state.wnext) {
                            copy -= state.wnext;
                            from = state.wsize - copy;
                        }
                        else {
                            from = state.wnext - copy;
                        }
                        if (copy > state.length) {
                            copy = state.length;
                        }
                        from_source = state.window;
                    }
                    else { /* copy from output */
                        from_source = output;
                        from = put - state.offset;
                        copy = state.length;
                    }
                    if (copy > left) {
                        copy = left;
                    }
                    left -= copy;
                    state.length -= copy;
                    do {
                        output[put++] = from_source[from++];
                    } while (--copy);
                    if (state.length === 0) {
                        state.mode = zlib_inflate.LEN;
                    }
                    break;
                case zlib_inflate.LIT:
                    if (left === 0) {
                        break inf_leave;
                    }
                    output[put++] = state.length;
                    left--;
                    state.mode = zlib_inflate.LEN;
                    break;
                case zlib_inflate.CHECK:
                    if (state.wrap) {
                        //=== NEEDBITS(32);
                        while (bits < 32) {
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            // Use '|' instead of '+' to make sure that result is signed
                            hold |= input[next++] << bits;
                            bits += 8;
                        }
                        //===//
                        _out -= left;
                        strm.total_out += _out;
                        state.total += _out;
                        if (_out) {
                            strm.adler = state.check =
                                /*UPDATE(state.check, put - _out, _out);*/
                                (state.flags ? ZLIB.zlib_crc32.crc32(state.check, output, _out, put - _out) : ZLIB.zlib_adler32.adler32(state.check, output, _out, put - _out));
                        }
                        _out = left;
                        // NB: crc32 stored as signed 32-bit int, zswap32 returns signed too
                        if ((state.flags ? hold : zlib_inflate.zswap32(hold)) !== state.check) {
                            strm.msg = 'incorrect data check';
                            state.mode = zlib_inflate.BAD;
                            break;
                        }
                        //=== INITBITS();
                        hold = 0;
                        bits = 0;
                        //===//
                        //Tracev((stderr, "inflate:   check matches trailer\n"));
                    }
                    state.mode = zlib_inflate.LENGTH;
                /* falls through */
                case zlib_inflate.LENGTH:
                    if (state.wrap && state.flags) {
                        //=== NEEDBITS(32);
                        while (bits < 32) {
                            if (have === 0) {
                                break inf_leave;
                            }
                            have--;
                            hold += input[next++] << bits;
                            bits += 8;
                        }
                        //===//
                        if (hold !== (state.total & 0xffffffff)) {
                            strm.msg = 'incorrect length check';
                            state.mode = zlib_inflate.BAD;
                            break;
                        }
                        //=== INITBITS();
                        hold = 0;
                        bits = 0;
                        //===//
                        //Tracev((stderr, "inflate:   length matches trailer\n"));
                    }
                    state.mode = zlib_inflate.DONE;
                /* falls through */
                case zlib_inflate.DONE:
                    ret = ZLIB.zlib_constants.Z_STREAM_END;
                    break inf_leave;
                case zlib_inflate.BAD:
                    ret = ZLIB.zlib_constants.Z_DATA_ERROR;
                    break inf_leave;
                case zlib_inflate.MEM:
                    return ZLIB.zlib_constants.Z_MEM_ERROR;
                case zlib_inflate.SYNC:
                /* falls through */
                default:
                    return ZLIB.zlib_constants.Z_STREAM_ERROR;
            }
        }
        // inf_leave <- here is real place for "goto inf_leave", emulated via "break inf_leave"
        /*
           Return from inflate(), updating the total counts and the check value.
           If there was no progress during the inflate() call, return a buffer
           error.  Call updatewindow() to create and/or update the window state.
           Note: a memory error from inflate() is non-recoverable.
         */
        //--- RESTORE() ---
        strm.next_out = put;
        strm.avail_out = left;
        strm.next_in = next;
        strm.avail_in = have;
        state.hold = hold;
        state.bits = bits;
        //---
        if (state.wsize || (_out !== strm.avail_out && state.mode < zlib_inflate.BAD &&
            (state.mode < zlib_inflate.CHECK || flush !== ZLIB.zlib_constants.Z_FINISH))) {
            if (zlib_inflate.updatewindow(strm, strm.output, strm.next_out, _out - strm.avail_out)) {
                state.mode = zlib_inflate.MEM;
                return ZLIB.zlib_constants.Z_MEM_ERROR;
            }
        }
        _in -= strm.avail_in;
        _out -= strm.avail_out;
        strm.total_in += _in;
        strm.total_out += _out;
        state.total += _out;
        if (state.wrap && _out) {
            strm.adler = state.check = /*UPDATE(state.check, strm.next_out - _out, _out);*/
                (state.flags ? ZLIB.zlib_crc32.crc32(state.check, output, _out, strm.next_out - _out) : ZLIB.zlib_adler32.adler32(state.check, output, _out, strm.next_out - _out));
        }
        strm.data_type = state.bits + (state.last ? 64 : 0) +
            (state.mode === zlib_inflate.TYPE ? 128 : 0) +
            (state.mode === zlib_inflate.LEN_ || state.mode === zlib_inflate.COPY_ ? 256 : 0);
        if (((_in === 0 && _out === 0) || flush === ZLIB.zlib_constants.Z_FINISH) && ret === ZLIB.zlib_constants.Z_OK) {
            ret = ZLIB.zlib_constants.Z_BUF_ERROR;
        }
        return ret;
    }
    ;
    static inflateEnd(strm) {
        if (!strm || !strm.state /*|| strm->zfree == (free_func)0*/) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        let state = strm.state;
        if (state.window) {
            state.window = null;
        }
        strm.state = null;
        return ZLIB.zlib_constants.Z_OK;
    }
    ;
    static inflateGetHeader(strm, head) {
        /* check state */
        if (!strm || !strm.state) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        let state = strm.state;
        if ((state.wrap & 2) === 0) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        /* save header structure */
        state.head = head;
        head.done = false;
        return ZLIB.zlib_constants.Z_OK;
    }
    static inflateSetDictionary(strm, dictionary) {
        const dictLength = dictionary.length;
        let state;
        let dictid;
        let ret;
        /* check state */
        if (!strm /* == ZLIB.Pako_constants.Z_NULL */ || !strm.state /* == ZLIB.Pako_constants.Z_NULL */) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        state = strm.state;
        if (state.wrap !== 0 && state.mode !== zlib_inflate.DICT) {
            return ZLIB.zlib_constants.Z_STREAM_ERROR;
        }
        /* check for correct dictionary identifier */
        if (state.mode === zlib_inflate.DICT) {
            dictid = 1; /* adler32(0, null, 0)*/
            /* dictid = adler32(dictid, dictionary, dictLength); */
            dictid = ZLIB.zlib_adler32.adler32(dictid, dictionary, dictLength, 0);
            if (dictid !== state.check) {
                return ZLIB.zlib_constants.Z_DATA_ERROR;
            }
        }
        /* copy dictionary to window using updatewindow(), which will amend the
         existing dictionary if appropriate */
        ret = zlib_inflate.updatewindow(strm, dictionary, dictLength, dictLength);
        if (ret) {
            state.mode = zlib_inflate.MEM;
            return ZLIB.zlib_constants.Z_MEM_ERROR;
        }
        state.havedict = 1;
        // Tracev((stderr, "inflate:   dictionary set\n"));
        return ZLIB.zlib_constants.Z_OK;
    }
}
exports.zlib_inflate = zlib_inflate;
zlib_inflate.CODES = 0;
zlib_inflate.LENS = 1;
zlib_inflate.DISTS = 2;
/* STATES ====================================================================*/
/* ===========================================================================*/
zlib_inflate.HEAD = 1; /* i: waiting for magic header */
zlib_inflate.FLAGS = 2; /* i: waiting for method and flags (gzip) */
zlib_inflate.TIME = 3; /* i: waiting for modification time (gzip) */
zlib_inflate.OS = 4; /* i: waiting for extra flags and operating system (gzip) */
zlib_inflate.EXLEN = 5; /* i: waiting for extra length (gzip) */
zlib_inflate.EXTRA = 6; /* i: waiting for extra bytes (gzip) */
zlib_inflate.NAME = 7; /* i: waiting for end of file name (gzip) */
zlib_inflate.COMMENT = 8; /* i: waiting for end of comment (gzip) */
zlib_inflate.HCRC = 9; /* i: waiting for header crc (gzip) */
zlib_inflate.DICTID = 10; /* i: waiting for dictionary check value */
zlib_inflate.DICT = 11; /* waiting for inflateSetDictionary() call */
zlib_inflate.TYPE = 12; /* i: waiting for type bits, including last-flag bit */
zlib_inflate.TYPEDO = 13; /* i: same, but skip check to exit inflate on new block */
zlib_inflate.STORED = 14; /* i: waiting for stored size (length and complement) */
zlib_inflate.COPY_ = 15; /* i/o: same as COPY below, but only first time in */
zlib_inflate.COPY = 16; /* i/o: waiting for input or output to copy stored block */
zlib_inflate.TABLE = 17; /* i: waiting for dynamic block table lengths */
zlib_inflate.LENLENS = 18; /* i: waiting for code length code lengths */
zlib_inflate.CODELENS = 19; /* i: waiting for length/lit and distance code lengths */
zlib_inflate.LEN_ = 20; /* i: same as LEN below, but only first time in */
zlib_inflate.LEN = 21; /* i: waiting for length/lit/eob code */
zlib_inflate.LENEXT = 22; /* i: waiting for length extra bits */
zlib_inflate.DIST = 23; /* i: waiting for distance code */
zlib_inflate.DISTEXT = 24; /* i: waiting for distance extra bits */
zlib_inflate.MATCH = 25; /* o: waiting for output space to copy string */
zlib_inflate.LIT = 26; /* o: waiting for output space to write literal */
zlib_inflate.CHECK = 27; /* i: waiting for 32-bit check value */
zlib_inflate.LENGTH = 28; /* i: waiting for 32-bit length (gzip) */
zlib_inflate.DONE = 29; /* finished check, done -- remain here until reset */
zlib_inflate.BAD = 30; /* got a data error -- remain here until reset */
zlib_inflate.MEM = 31; /* got an inflate() memory error -- remain here until reset */
zlib_inflate.SYNC = 32; /* looking for synchronization bytes to restart inflate() */
/* ===========================================================================*/
zlib_inflate.ENOUGH_LENS = 852;
zlib_inflate.ENOUGH_DISTS = 592;
//const ENOUGH =  (ENOUGH_LENS+ENOUGH_DISTS);
zlib_inflate.MAX_WBITS = 15;
/* 32K LZ77 window */
zlib_inflate.DEF_WBITS = zlib_inflate.MAX_WBITS;
/*
 Return state with length and distance decoding tables and index sizes set to
 fixed code decoding.  Normally this returns fixed tables from inffixed.h.
 If BUILDFIXED is defined, then instead this routine builds the tables the
 first time it's called, and returns those tables the first time and
 thereafter.  This reduces the size of the code by about 2K bytes, in
 exchange for a little execution time.  However, BUILDFIXED should not be
 used for threaded applications, since the rewriting of the tables and virgin
 may not be thread-safe.
 */
zlib_inflate.virgin = true;
//# sourceMappingURL=inflate.js.map