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

export class zlib_constants
{

    /* Allowed flush values; see deflate() and inflate() below for details */
    public static readonly Z_NO_FLUSH: number = 0;
    public static readonly Z_PARTIAL_FLUSH: number = 1;
    public static readonly Z_SYNC_FLUSH: number = 2;
    public static readonly Z_FULL_FLUSH: number = 3;
    public static readonly Z_FINISH: number = 4;
    public static readonly Z_BLOCK: number = 5;
    public static readonly Z_TREES: number = 6;

    /* Return codes for the compression/decompression functions. Negative values
    * are errors, positive values are used for special but normal events.
    */
    public static readonly Z_OK: number = 0;
    public static readonly Z_STREAM_END: number = 1;
    public static readonly Z_NEED_DICT: number = 2;
    public static readonly Z_ERRNO: number = -1;
    public static readonly Z_STREAM_ERROR: number = -2;
    public static readonly Z_DATA_ERROR: number = -3;
    public static readonly Z_MEM_ERROR: number = -4;
    public static readonly Z_BUF_ERROR: number = -5;
    //Z_VERSION_ERROR: -6,

    /* compression levels */
    public static readonly Z_NO_COMPRESSION: number = 0;
    public static readonly Z_BEST_SPEED: number = 1;
    public static readonly Z_BEST_COMPRESSION: number = 9;
    public static readonly Z_DEFAULT_COMPRESSION: number = -1;

    public static readonly Z_FILTERED: number = 1;
    public static readonly Z_HUFFMAN_ONLY: number = 2;
    public static readonly Z_RLE: number = 3;
    public static readonly Z_FIXED: number = 4;
    public static readonly Z_DEFAULT_STRATEGY: number = 0;

    /* Possible values of the data_type field (though see inflate()) */
    public static readonly Z_BINARY: number = 0;
    public static readonly Z_TEXT: number = 1;
    //Z_ASCII:                1, // = Z_TEXT (deprecated)
    public static readonly Z_UNKNOWN: number = 2;

    /* The deflate compression method */
    public static readonly Z_DEFLATED: number = 8;
    //Z_NULL:                 null // Use -1 or null inline, depending on var type
}
