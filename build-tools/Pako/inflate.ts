import * as PAKO from "./Pakofull.js";
import * as ZLIB from "./zlib/zlibfull.js";
import * as UTILS from "./utils/utilsFull.js";

/* ===========================================================================*/

/**
 * class Inflate
 *
 * Generic JS-style wrapper for zlib calls. If you don't need
 * streaming behaviour - use more simple functions: [[inflate]]
 * and [[inflateRaw]].
 **/

/* internal
 * inflate.chunks -> Array
 *
 * Chunks of output data, if [[Inflate#onData]] not overridden.
 **/

/**
 * Inflate.result -> Uint8Array|String
 *
 * Uncompressed result, generated by default [[Inflate#onData]]
 * and [[Inflate#onEnd]] handlers. Filled after you push last chunk
 * (call [[Inflate#push]] with `Z_FINISH` / `true` param).
 **/

/**
 * Inflate.err -> Number
 *
 * Error code after inflate finished. 0 (Z_OK) on success.
 * Should be checked if broken data possible.
 **/

/**
 * Inflate.msg -> String
 *
 * Error message, if [[Inflate.err]] != 0
 **/

/**
 * new Inflate(options)
 * - options (Object): zlib inflate options.
 *
 * Creates new inflator instance with specified params. Throws exception
 * on bad params. Supported options:
 *
 * - `windowBits`
 * - `dictionary`
 *
 * [http://zlib.net/manual.html#Advanced](http://zlib.net/manual.html#Advanced)
 * for more information on these.
 *
 * Additional options, for internal needs:
 *
 * - `chunkSize` - size of generated data chunks (16K by default)
 * - `raw` (Boolean) - do raw inflate
 * - `to` (String) - if equal to 'string', then result will be converted
 *   from utf8 to utf16 (javascript) string. When string output requested,
 *   chunk length can differ from `chunkSize`, depending on content.
 *
 * By default, when no options set, autodetect deflate/gzip data format via
 * wrapper header.
 *
 * ##### Example:
 *
 * ```javascript
 * const pako = require('pako')
 * const chunk1 = new Uint8Array([1,2,3,4,5,6,7,8,9])
 * const chunk2 = new Uint8Array([10,11,12,13,14,15,16,17,18,19]);
 *
 * const inflate = new pako.Inflate({ level: 3});
 *
 * inflate.push(chunk1, false);
 * inflate.push(chunk2, true);  // true -> last chunk
 *
 * if (inflate.err) { throw new Error(inflate.err); }
 *
 * console.log(inflate.result);
 * ```
 **/

export class Pako_inflate_option
{
    public chunkSize: number = 1024 * 64;
    public windowBits: number = 15;
    public to: string = '';
    public raw: boolean = false;
    public dictionary: Uint8Array | null = null;

    constructor(CustOption?: {})
    {
        Pako_inflate_option.ApplyCustomOptions(this, CustOption)

    }
    public static ApplyCustomOptions(StdOption: Pako_inflate_option, CustOption?: {})
    {
        if (typeof CustOption != "undefined")
        {
            let stdKeys: string[] = Object.keys(StdOption);
            let custKeys: string[] = Object.keys(CustOption);
            for (let i: number = 0; i < custKeys.length; i++)
            {
                if (stdKeys.indexOf(custKeys[i]) < 0) throw "Invalid Pako option name '" + custKeys[i] + "', check Pako_inflate_option class";
                let srcType: string = typeof (Reflect.get(CustOption, custKeys[i]));
                let trgtType: string = typeof (Reflect.get(StdOption, custKeys[i]));
                if (srcType != trgtType)
                {
                    throw  "Invalid Pako type for option '" + custKeys[i] + "' (expected '" + trgtType + "', got '" + srcType + "')";
                }
                Reflect.set(StdOption, custKeys[i], Reflect.get(CustOption, custKeys[i]));
            }
        }
    }

}

export class Pako_Inflate
{
    public options: Pako_inflate_option;
    public err: number = 0;      // error code, if happens (0 = Z_OK)
    private msg: string = '';     // error message
    private ended: boolean = false;  // used to avoid multiple onEnd() calls
    private chunks: Uint8Array[] | string[];     // chunks of compressed data
    private strm: PAKO.zlib_ZStream;
    private header: PAKO.zlib_GZheader;
    public result: Uint8Array | string | null = null;

    constructor(options: {})
    {
        this.options = new Pako_inflate_option(options);
        const opt = this.options;

        // Force window size for `raw` data, if not set directly,
        // because we have no header for autodetect.
        if (opt.raw && (opt.windowBits >= 0) && (opt.windowBits < 16))
        {
            opt.windowBits = -opt.windowBits;
            if (opt.windowBits === 0)
            { opt.windowBits = -15; }
        }

        // If `windowBits` not defined (and mode not raw) - set autodetect flag for gzip/deflate
        if ((opt.windowBits >= 0) && (opt.windowBits < 16) &&
            !(options && (<any>options).windowBits))
        {
            opt.windowBits += 32;
        }

        // Gzip header has no info about windows size, we can do autodetect only
        // for deflate. So, if window size not set, force it to max when gzip possible
        if ((opt.windowBits > 15) && (opt.windowBits < 48))
        {
            // bit 3 (16) -> gzipped data
            // bit 4 (32) -> autodetect gzip/deflate
            if ((opt.windowBits & 15) === 0)
            {
                opt.windowBits |= 15;
            }
        }

        this.err = 0;      // error code, if happens (0 = Z_OK)
        this.msg = '';     // error message
        this.ended = false;  // used to avoid multiple onEnd() calls
        this.chunks = [];     // chunks of compressed data

        this.strm = new PAKO.zlib_ZStream();
        this.strm.avail_out = 0;

        let status = PAKO.zlib_inflate.inflateInit2(
            this.strm,
            opt.windowBits
        );

        if (status !== PAKO.zlib_constants.Z_OK)
        {
            throw new Error(PAKO.zlib_messages.msg(status));
        }

        this.header = new PAKO.zlib_GZheader();

        PAKO.zlib_inflate.inflateGetHeader(this.strm, this.header);

        // Setup dictionary
        if (opt.dictionary)
        {
            // Convert data if needed
            if (typeof opt.dictionary === 'string')
            {
                opt.dictionary = PAKO.Pako_strings.string2buf(opt.dictionary);
            }
            else if (toString.call(opt.dictionary) === '[object ArrayBuffer]')
            {
                opt.dictionary = new Uint8Array(opt.dictionary);
            }
            if (opt.raw)
            { //In raw mode we need to set the dictionary early
                status = PAKO.zlib_inflate.inflateSetDictionary(this.strm, opt.dictionary);
                if (status !== PAKO.zlib_constants.Z_OK)
                {
                    throw new Error(PAKO.zlib_messages.msg(status));
                }
            }
        }
    }

    /**
     * Inflate#push(data[, flush_mode]) -> Boolean
     * - data (Uint8Array|ArrayBuffer): input data
     * - flush_mode (Number|Boolean): 0..6 for corresponding Z_NO_FLUSH..Z_TREE
     *   flush modes. See constants. Skipped or `false` means Z_NO_FLUSH,
     *   `true` means Z_FINISH.
     *
     * Sends input data to inflate pipe, generating [[Inflate#onData]] calls with
     * new output chunks. Returns `true` on success. If end of stream detected,
     * [[Inflate#onEnd]] will be called.
     *
     * `flush_mode` is not needed for normal operation, because end of stream
     * detected automatically. You may try to use it for advanced things, but
     * this functionality was not tested.
     *
     * On fail call [[Inflate#onEnd]] with error code and return false.
     *
     * ##### Example
     *
     * ```javascript
     * push(chunk, false); // push one of data chunks
     * ...
     * push(chunk, true);  // push last chunk
     * ```
     **/
    public push(data: Uint8Array, flush_mode: Number | Boolean)
    {
        const strm = this.strm;
        const chunkSize = this.options.chunkSize;
        const dictionary = this.options.dictionary;
        let status: number
        let _flush_mode: number
        let last_avail_out: number;

        if (this.ended) return false;

        if (flush_mode === ~~flush_mode)
        {
            _flush_mode = <number>flush_mode;
        }
        else
        {
            _flush_mode = flush_mode === true ? PAKO.zlib_constants.Z_FINISH : PAKO.zlib_constants.Z_NO_FLUSH;
        }

        // Convert data if needed
        if (toString.call(data) === '[object ArrayBuffer]')
        {
            strm.input = new Uint8Array(data);
        }
        else
        {
            strm.input = data;
        }

        strm.next_in = 0;
        strm.avail_in = strm.input.length;

        for (; ;)
        {
            if (strm.avail_out === 0)
            {
                strm.output = new Uint8Array(chunkSize);
                strm.next_out = 0;
                strm.avail_out = chunkSize;
            }

            status = PAKO.zlib_inflate.inflate(strm, _flush_mode);

            if (status === PAKO.zlib_constants.Z_NEED_DICT && dictionary)
            {
                status = PAKO.zlib_inflate.inflateSetDictionary(strm, dictionary);

                if (status === PAKO.zlib_constants.Z_OK)
                {
                    status = PAKO.zlib_inflate.inflate(strm, _flush_mode);
                }
                else if (status === PAKO.zlib_constants.Z_DATA_ERROR)
                {
                    // Replace code with more verbose
                    status = PAKO.zlib_constants.Z_NEED_DICT;
                }
            }

            // Skip snyc markers if more data follows and not raw mode
            while (strm.avail_in > 0 &&
            status === PAKO.zlib_constants.Z_STREAM_END &&
            (<ZLIB.zlib_DeflateState>strm.state).wrap > 0 &&
            data[strm.next_in] !== 0)
            {
                PAKO.zlib_inflate.inflateReset(strm);
                status = PAKO.zlib_inflate.inflate(strm, _flush_mode);
            }

            switch (status)
            {
            case PAKO.zlib_constants.Z_STREAM_ERROR:
            case PAKO.zlib_constants.Z_DATA_ERROR:
            case PAKO.zlib_constants.Z_NEED_DICT:
            case PAKO.zlib_constants.Z_MEM_ERROR:
                this.onEnd(status);
                this.ended = true;
                return false;
            }

            // Remember real `avail_out` value, because we may patch out buffer content
            // to align utf8 strings boundaries.
            last_avail_out = strm.avail_out;

            if (strm.next_out)
            {
                if (strm.avail_out === 0 || status === PAKO.zlib_constants.Z_STREAM_END)
                {

                    if (this.options.to === 'string')
                    {

                        let next_out_utf8 = PAKO.Pako_strings.utf8border(strm.output, strm.next_out);

                        let tail = strm.next_out - next_out_utf8;
                        let utf8str = PAKO.Pako_strings.buf2string(strm.output, next_out_utf8);

                        // move tail & realign counters
                        strm.next_out = tail;
                        strm.avail_out = chunkSize - tail;
                        if (tail) strm.output.set(strm.output.subarray(next_out_utf8, next_out_utf8 + tail), 0);

                        this.onData(utf8str);

                    }
                    else
                    {
                        this.onData(strm.output.length === strm.next_out ? strm.output : strm.output.subarray(0, strm.next_out));
                    }
                }
            }

            // Must repeat iteration if out buffer is full
            if (status === PAKO.zlib_constants.Z_OK && last_avail_out === 0) continue;

            // Finalize if end of stream reached.
            if (status === PAKO.zlib_constants.Z_STREAM_END)
            {
                status = PAKO.zlib_inflate.inflateEnd(this.strm);
                this.onEnd(status);
                this.ended = true;
                return true;
            }

            if (strm.avail_in === 0) break;
        }

        return true;
    };

    /**
     * Inflate#onData(chunk) -> Void
     * - chunk (Uint8Array|String): output data. When string output requested,
     *   each chunk will be string.
     *
     * By default, stores data blocks in `chunks[]` property and glue
     * those in `onEnd`. Override this handler, if you need another behaviour.
     **/
    public onData(chunk: Uint8Array | string)
    {
        this.chunks[this.chunks.length] = chunk;
    }

    /**
     * Inflate#onEnd(status) -> Void
     * - status (Number): inflate status. 0 (Z_OK) on success,
     *   other if not.
     *
     * Called either after you tell inflate that the input stream is
     * complete (Z_FINISH). By default - join collected chunks,
     * free memory and fill `results` / `err` properties.
     **/
    public onEnd(status: number)
    {
        // On success - join
        if (status === PAKO.zlib_constants.Z_OK)
        {
            if (this.options.to === 'string')
            {
                this.result = (<string[]>this.chunks).join('');
            }
            else
            {
                this.result = UTILS.utils_common.flattenChunks(<Uint8Array[]>this.chunks);
            }
        }
        this.chunks = [];
        this.err = status;
        this.msg = this.strm.msg;
    };

    /**
     * inflate(data[, options]) -> Uint8Array|String
     * - data (Uint8Array): input data to decompress.
     * - options (Object): zlib inflate options.
     *
     * Decompress `data` with inflate/ungzip and `options`. Autodetect
     * format via wrapper header by default. That's why we don't provide
     * separate `ungzip` method.
     *
     * Supported options are:
     *
     * - windowBits
     *
     * [http://zlib.net/manual.html#Advanced](http://zlib.net/manual.html#Advanced)
     * for more information.
     *
     * Sugar (options):
     *
     * - `raw` (Boolean) - say that we work with raw stream, if you don't wish to specify
     *   negative windowBits implicitly.
     * - `to` (String) - if equal to 'string', then result will be converted
     *   from utf8 to utf16 (javascript) string. When string output requested,
     *   chunk length can differ from `chunkSize`, depending on content.
     *
     *
     * ##### Example:
     *
     * ```javascript
     * const pako = require('pako');
     * const input = pako.deflate(new Uint8Array([1,2,3,4,5,6,7,8,9]));
     * let output;
     *
     * try {
     *   output = pako.inflate(input);
     * } catch (err)
     *   console.log(err);
     * }
     * ```
     **/
    public static inflate(input: Uint8Array, options: {}): Uint8Array | string | null
    {
        const inflator = new Pako_Inflate(options);

        inflator.push(input, false);

        // That will never happens, if you don't cheat with options :)
        if (inflator.err) throw inflator.msg || PAKO.zlib_messages.msg(inflator.err);

        return inflator.result;
    }

    /**
     * inflateRaw(data[, options]) -> Uint8Array|String
     * - data (Uint8Array): input data to decompress.
     * - options (Object): zlib inflate options.
     *
     * The same as [[inflate]], but creates raw data, without wrapper
     * (header and adler32 crc).
     **/
    public inflateRaw(input: Uint8Array, options: {}): Uint8Array | string | null
    {

        (<any>options).raw = true;
        return Pako_Inflate.inflate(input, options);
    }

    /**
     * ungzip(data[, options]) -> Uint8Array|String
     * - data (Uint8Array): input data to decompress.
     * - options (Object): zlib inflate options.
     *
     * Just shortcut to [[inflate]], because it autodetects format
     * by header.content. Done for convenience.
     **/
    public static ungzip(input: Uint8Array, options: {}): Uint8Array | string | null
    {
        return Pako_Inflate.inflate(input, options);
    }
}

/*
module.exports.Inflate = Inflate;
module.exports.inflate = inflate;
module.exports.inflateRaw = inflateRaw;
module.exports.ungzip = inflate;
module.exports.constants = require('./zlib/constants');
*/