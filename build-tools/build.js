"use strict";
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
// This script is intended to be used from the package root (lib directory), from npm scripts.
//
// Usage:
//
//   npm run build
//   => bump version number to next pre-release suffix and update index.js
//
//   npm run build -- 1.10.21818
//   => set official version number and update index.js
//
const fs = __importStar(require("fs"));
const semver = __importStar(require("semver"));
const ts = __importStar(require("typescript"));
const process = __importStar(require("process"));
const esbuild = __importStar(require("esbuild"));
const Pako = __importStar(require("./Pako/Pakofull.js"));
function patchVersionInFile(newver, str_filename) {
    let pattern = '/* version number patched automatically */';
    let jsFile = fs.readFileSync(str_filename);
    let pos = jsFile.indexOf(pattern);
    if (pos < 0) {
        console.log('*** Warning, cannot patch ' + str_filename + ', pattern not found !');
    }
    else {
        pos += pattern.length;
        let endMark = jsFile.indexOf(';', pos);
        let patch = "'" + newver + "'";
        let res = Buffer.alloc(pos + patch.length + jsFile.length - endMark);
        jsFile.copy(res, 0, 0, pos);
        res.write(patch, pos);
        jsFile.copy(res, pos + patch.length, endMark);
        fs.writeFileSync(str_filename, res);
    }
}
function setVersion(str_newver) {
    // update version number is package.json
    let json = JSON.parse(fs.readFileSync('package.json', 'utf8'));
    let newver;
    console.log('Was at version ' + json.version);
    if (str_newver) {
        // argument is new version number
        newver = semver.clean(str_newver);
    }
    else {
        // bump local revision number
        newver = semver.inc(json.version, 'prerelease', 'dev');
    }
    if (!newver) {
        console.log('Invalid version number: ' + process.argv[2]);
        process.exit(1);
    }
    console.log('Now at version ' + newver);
    json.version = newver;
    fs.writeFileSync("package.json", JSON.stringify(json, null, 2), 'utf-8');
}
function findFilesRecursively(path, pattern) {
    let result = [];
    fs.readdirSync(path).forEach((name) => {
        if (name[0] != '.') {
            let subpath = path + '/' + name;
            if (fs.statSync(subpath).isDirectory()) {
                result.push(...findFilesRecursively(subpath, pattern));
            }
            else if (pattern.test(name)) {
                result.push(subpath);
            }
        }
    });
    return result;
}
/*
 * Trivial PHP 8.x to 7.x converter: mainly remove attribute typing, plus small details
 */
function downgradePHP(php8code) {
    return php8code
        .replace(/JSON_THROW_ON_ERROR/g, '0')
        .replace(/\(Throwable\)/gi, '(Throwable $e)')
        .replace(/: *void/g, '') // void return >= PHP 7.1
        .replace(/string\|int\s+/g, '') // union type >= PHP 8.0
        .replace(/(public\s+|protected\s+|private\s+)(static\s+|)\??(array|bool|float|int|string|object|self|parent|interable|mixed|[A-Z]\w+)\s+(\$\w+\s*(=[^;]+|);)/g, '$1$2$4');
}
/*
 * Bundle multiple PHP files into a single file, in the right order.
 * Removes the outside <?php...?> markers, as well as
 * - include statements
 * - declare statements
 * - empty lines
 */
function bundlePHP(srcdir, entrypoint, version) {
    console.log('Bundling files for ' + srcdir + '/' + entrypoint + '...');
    let incPattern = /include(_once|)\(["']([^"']+.php)["']\);/g;
    let result = "const VERSION = " + JSON.stringify(version) + ";\n";
    let inputFiles = {};
    let fileList = [entrypoint];
    for (let i = 0; i < fileList.length; i++) {
        let relPath = fileList[i];
        let fullPath = srcdir + '/' + relPath;
        if (!fs.existsSync(fullPath)) {
            console.log('Unresolved dependency: ' + relPath);
            continue;
        }
        console.log('Processing: ' + relPath);
        let dirpath = relPath.replace(/[^\/]+$/g, '');
        let content = fs.readFileSync(fullPath, 'utf-8');
        let deps = [];
        let execarr;
        while ((execarr = incPattern.exec(content)) !== null) {
            let included = dirpath + execarr[2];
            deps.push(included);
            if (!inputFiles[included]) {
                fileList.push(included);
            }
        }
        inputFiles[relPath] = { content: content, deps: deps, done: false };
    }
    let completelyDone = false;
    while (!completelyDone) {
        let somethingDone = false;
        completelyDone = true;
        for (let relPath in inputFiles) {
            let file = inputFiles[relPath];
            if (file.done)
                continue;
            file.done = true;
            for (let dep of file.deps) {
                if (!inputFiles[dep].done) {
                    file.done = false;
                    break;
                }
            }
            if (file.done) {
                result += file.content
                    .replace(/^\s*(include|include_once|declare)\([^)]+\);\s*[\r\n]/gm, '')
                    .replace(/\/\*(.|\r|\n)*?\*\//g, '')
                    .replace(/^<\?php\s+/, '')
                    .replace(/\?>\s*$/, '')
                    .replace(/^\s*[\r\n]/gm, '') + '\n';
                somethingDone = true;
            }
            else {
                completelyDone = false;
            }
        }
        if (!somethingDone) {
            console.log('Cannot bundle file, circular dependency remaining for:');
            for (let relPath in inputFiles) {
                let file = inputFiles[relPath];
                if (!file.done) {
                    console.log('- ' + relPath);
                }
            }
            break;
        }
    }
    return result;
}
/*
 * Bundle multiple PHP files into a single file, in the right order
 */
function createPHPinstaller(srcdir, distdir, instdir, bundleName, version, banner) {
    let distpath = distdir + '/' + bundleName;
    let instpath = instdir + '/' + bundleName;
    let initFile = fs.readFileSync(srcdir + '/vhub4web-init.php', 'utf-8')
        .replace(/include_once\([^)]+\);/m, `include_once(__DIR__.'/${bundleName}-php'.PHP_MAJOR_VERSION.'.php');`);
    let bundle = '<?php /* ' + banner + ' - www.yoctopuce.com */\ndeclare(strict_types=1);\n' +
        bundlePHP(srcdir, 'VHubServer.php', version);
    let installerFile = '<?php /* ' + banner + ' Installer - www.yoctopuce.com */\ndeclare(strict_types=1);\n' +
        '/* This data blob is generated by build.ts. It contains VirtualHub-4web php code, gzipped */\n';
    let gzbundle = Pako.Pako_Deflate.gzip(bundle, { level: 9 });
    installerFile += "$phpCode = 'data:text/plain;base64," + Buffer.from(gzbundle).toString('base64') + "';\n";
    let yfsImage = fs.readFileSync(srcdir + '/YFSImg.yfs');
    installerFile += "$yfsImage = 'data:text/plain;base64," + Buffer.from(yfsImage).toString('base64') + "';\n";
    installerFile += "$initCode = 'data:text/plain;base64," + Buffer.from(initFile).toString('base64') + "';\n";
    installerFile += bundlePHP(srcdir, 'Installer.php', version);
    // Create distribution files
    fs.mkdirSync(distpath.slice(0, distpath.lastIndexOf('/')), { recursive: true });
    fs.mkdirSync(instpath.slice(0, instpath.lastIndexOf('/')), { recursive: true });
    fs.writeFileSync(distdir + '/YFSImg.yfs', yfsImage);
    fs.writeFileSync(distpath + '-php8.php', bundle, 'utf-8');
    fs.writeFileSync(distpath + '-php7.php', downgradePHP(bundle), 'utf-8');
    fs.writeFileSync(distpath + '-init.php', initFile, 'utf-8');
    fs.writeFileSync(instpath + '-installer.php', installerFile);
}
function setupPHPtestEnv(installer, testdir) {
    let prevInstallers = findFilesRecursively(testdir, /^vhub4web-installer.*\.php$/);
    for (let prevFile of prevInstallers) {
        fs.unlinkSync(prevFile);
    }
    fs.copyFile(installer, testdir + '/vhub4web-installer.php', () => { });
}
async function transpileTS(srcdir, objdir) {
    const cwd = process.cwd().replace(/\\/g, '/');
    let options = {
        strict: true,
        allowSyntheticDefaultImports: true,
        esModuleInterop: true,
        skipLibCheck: true,
        target: ts.ScriptTarget.ES2017,
        "outDir": objdir
    };
    console.log('Transpiling ' + srcdir + ' TypeScript files...');
    let inputFiles = findFilesRecursively(srcdir, /\.ts$/);
    let program = ts.createProgram(inputFiles, options);
    let emitResult = program.emit();
    let allDiagnostics = ts.getPreEmitDiagnostics(program).concat(emitResult.diagnostics);
    console.log(allDiagnostics.length + ' messages generated by TypeScript compiler');
    allDiagnostics.forEach(diagnostic => {
        if (diagnostic.file) {
            let { line, character } = diagnostic.file.getLineAndCharacterOfPosition(diagnostic.start);
            let message = ts.flattenDiagnosticMessageText(diagnostic.messageText, "\n");
            console.log(`${diagnostic.file.fileName} (${line + 1},${character + 1}): ${message}`);
        }
        else {
            console.log(ts.flattenDiagnosticMessageText(diagnostic.messageText, "\n"));
        }
    });
}
async function bundleTS(objdir, distdir, bundleName, banner) {
    await esbuild.build({
        entryPoints: [objdir + '/index.js'],
        bundle: true,
        target: 'es2017',
        format: 'esm',
        sourcemap: true,
        banner: banner,
        outfile: distdir + '/' + bundleName + '.js',
    });
    await esbuild.build({
        entryPoints: [objdir + '/index.js'],
        bundle: true,
        target: 'es2017',
        format: 'esm',
        sourcemap: false,
        minify: true,
        banner: banner,
        outfile: distdir + '/' + bundleName + '.min.js',
    });
}
async function createJSinstaller(srcdir, objdir, distdir, bundleName, banner) {
    await transpileTS(srcdir, objdir);
    await bundleTS(objdir, distdir, bundleName, '/* ' + banner + ' */');
}
let args = process.argv.slice(2);
if (args.length == 0) {
    console.log("command expected");
}
else {
    switch (args[0]) {
        case "newbuild":
            setVersion(args[1]);
            break;
        case "build":
            let json = JSON.parse(fs.readFileSync('package.json', 'utf8'));
            let banner = 'VirtualHub-4web (version ' + json.version + ')';
            console.log('Building version ' + json.version);
            // Build PHP version
            createPHPinstaller('PHP-Version/src', 'PHP-Version/dist', 'PHP-Version/installer', 'vhub4web', json.version, banner);
            setupPHPtestEnv('PHP-Version/installer/vhub4web-installer.php', 'PHP-Version/www/VirtualHub-4web');
            // Build NodeJS version when ready
            //-- createJSinstaller('NodeJS-Version/src', 'NodeJS-Version/obj', 'NodeJS-Version/dist', 'vhub4web', banner);
            // Create data directory used for debugging
            if (!fs.existsSync('data')) {
                fs.mkdirSync('data');
            }
            break;
    }
}
//# sourceMappingURL=build.js.map