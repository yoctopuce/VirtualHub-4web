'use strict';
Object.defineProperty(exports, "__esModule", { value: true });
exports.zlib_messages = void 0;
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
class zlib_messages {
    static msg(index) {
        switch (index) {
            case 2:
                return 'need dictionary'; /* Z_NEED_DICT       2  */
            case 1:
                return 'stream end'; /* Z_STREAM_END      1  */
            case 0:
                return ''; /* Z_OK              0  */
            case -1:
                return 'file error'; /* Z_ERRNO         (-1) */
            case -2:
                return 'stream error'; /* Z_STREAM_ERROR  (-2) */
            case -3:
                return 'data error'; /* Z_DATA_ERROR    (-3) */
            case -4:
                return 'insufficient memory'; /* Z_MEM_ERROR     (-4) */
            case -5:
                return 'buffer error'; /* Z_BUF_ERROR     (-5) */
            case -6:
                return 'incompatible version'; /* Z_VERSION_ERROR (-6) */
            default:
                return 'unknowwn error'; /* Yes I know, it's lame. */
        }
    }
}
exports.zlib_messages = zlib_messages;
//# sourceMappingURL=messages.js.map