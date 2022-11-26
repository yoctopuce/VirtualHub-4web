"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.utils_common = void 0;
class utils_common {
    // This was orignaly used to manipulate deflate/inflate options
    // stored in anonymous structures
    // Yoctopuce replaced it with a proper options object with name
    // setting names and types check (see Pako_inflate_option and
    // Pako_deflate_option)
    //  public static assign  (obj : any, /*from1, from2, from3, ...*/)
    //    {
    //      const sources : any[] = Array.prototype.slice.call(arguments, 1);
    //      while (sources.length) {
    //     const source = sources.shift();
    //       if (!source) { continue; }
    //
    //       if (typeof source !== 'object') {
    //        throw new TypeError(source + 'must be non-object');
    //      }
    //
    //
    //    for (const p : string in source) {
    //      if (p.hasOwnProperty (source)) {
    //        obj[p] = source[p];
    //      }
    //    }
    //  }
    //
    //  return obj;
    //};
    // Join array of chunks to single array.
    static flattenChunks(chunks) {
        // calculate data length
        let len = 0;
        for (let i = 0, l = chunks.length; i < l; i++) {
            len += chunks[i].length;
        }
        // join chunks
        const result = new Uint8Array(len);
        for (let i = 0, pos = 0, l = chunks.length; i < l; i++) {
            let chunk = chunks[i];
            result.set(chunk, pos);
            pos += chunk.length;
        }
        return result;
    }
}
exports.utils_common = utils_common;
//# sourceMappingURL=common.js.map