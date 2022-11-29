pako
==========================================


> zlib library for Typescript. Ported by [Yoctopuce](https://www.yoctopuce.com) from
> the [PAKO Javascript library](https://github.com/nodeca/pako)

__Why pako is cool:__

- Results are binary equal to well known [zlib](http://www.zlib.net/) (now contains ported zlib v1.2.8).
- Almost as fast in modern JS engines as C implementation (see benchmarks).
- Works in browsers, you can browserify any separate component.

This project was done to understand how fast JS can be and is it necessary to
develop native C modules for CPU-intensive tasks. Enjoy the result!

Notes
-----

Pako does not contain some specific zlib functions:

- __deflate__ - methods `deflateCopy`, `deflateBound`, `deflateParams`,
  `deflatePending`, `deflatePrime`, `deflateTune`.
- __inflate__ - methods `inflateCopy`, `inflateMark`,
  `inflatePrime`, `inflateGetDictionary`, `inflateSync`, `inflateSyncPoint`, `inflateUndermine`.
- High level inflate/deflate wrappers (classes) may not support some flush
  modes.

Pako Authors
-------

- Andrey Tupitsin [@anrd83](https://github.com/andr83)
- Vitaly Puzrin [@puzrin](https://github.com/puzrin)

Authors Personal thanks to:

- Vyacheslav Egorov ([@mraleph](https://github.com/mraleph)) for his awesome
  tutorials about optimising JS code for v8, [IRHydra](http://mrale.ph/irhydra/)
  tool and his advices.
- David Duponchel ([@dduponchel](https://github.com/dduponchel)) for help with
  testing.

Original implementation (in C):

- [zlib](http://zlib.net/) by Jean-loup Gailly and Mark Adler.

License
-------

- MIT - all files, except `/lib/zlib` folder
- ZLIB - `/lib/zlib` content
