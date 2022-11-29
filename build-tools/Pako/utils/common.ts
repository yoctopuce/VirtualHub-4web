export class utils_common
{

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
    public static flattenChunks(chunks: Uint8Array[]): Uint8Array
    {
        // calculate data length
        let len: number = 0;

        for (let i: number = 0, l = chunks.length; i < l; i++)
        {
            len += chunks[i].length;
        }

        // join chunks
        const result: Uint8Array = new Uint8Array(len);

        for (let i: number = 0, pos: number = 0, l = chunks.length; i < l; i++)
        {
            let chunk = chunks[i];
            result.set(chunk, pos);
            pos += chunk.length;
        }

        return result;
    }
}
