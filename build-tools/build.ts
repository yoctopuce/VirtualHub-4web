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
import * as fs from 'fs';
import * as semver from 'semver';
import * as ts from 'typescript';
import * as process from 'process';
import * as esbuild from 'esbuild';
import * as Pako from "./Pako/Pakofull.js"

function patchVersionInFile(newver: string, str_filename: string)
{
    let pattern: string = '/* version number patched automatically */';
    let jsFile: Buffer = fs.readFileSync(str_filename);
    let pos: number = jsFile.indexOf(pattern);
    if(pos < 0) {
        console.log('*** Warning, cannot patch '+ str_filename+', pattern not found !');
    } else {
        pos += pattern.length;
        let endMark: number = jsFile.indexOf(';', pos);
        let patch: string = "'" + newver + "'";
        let res: Buffer = Buffer.alloc(pos + patch.length + jsFile.length-endMark);
        jsFile.copy(res, 0, 0, pos);
        res.write(patch, pos);
        jsFile.copy(res, pos + patch.length, endMark);
        fs.writeFileSync(str_filename, res);
    }
}

function setVersion(str_newver: string)
{
    // update version number is package.json
    let json: { version: string } = JSON.parse(fs.readFileSync('package.json', 'utf8'));
    let newver: string | null;
    console.log('Was at version ' + json.version);
    if (str_newver) {
        // argument is new version number
        newver = semver.clean(str_newver);
    } else {
        // bump local revision number
        newver = <string>semver.inc(json.version, 'prerelease', 'dev');
    }
    if (!newver) {
        console.log('Invalid version number: ' + process.argv[2]);
        process.exit(1);
    }
    console.log('Now at version ' + newver);
    json.version = newver;
    fs.writeFileSync("package.json", JSON.stringify(json, null, 2), 'utf-8');
}

function findFilesRecursively(path: string, pattern: RegExp): string[]
{
    let result: string[] = [];
    fs.readdirSync(path).forEach((name: string) => {
        if(name[0] != '.') {
            let subpath: string = path + '/' + name;
            if(fs.statSync(subpath).isDirectory()) {
                result.push(...findFilesRecursively(subpath, pattern));
            } else if(pattern.test(name)){
                result.push(subpath);
            }
        }
    })
    return result;
}

/*
 * Trivial PHP 8.x to 7.x converter: mainly remove attribute typing, plus small details
 */
function downgradePHP(php8code: string): string
{
    return php8code
        .replace(/JSON_THROW_ON_ERROR/g,'0')
        .replace(/\(Throwable\)/gi, '(Throwable $e)')
        .replace(/: *(void|mixed)/g, '') // no declared void or mixed return types
        .replace(/mixed/g, '')           // mixed type declaration (and union types) >= PHP 8.0
        .replace(/(public\s+|protected\s+|private\s+)(static\s+|)\??(array|bool|float|int|string|object|self|parent|interable|mixed|[A-Z]\w+)\s+(\$\w+\s*(=[^;]+|);)/g, '$1$2$4');
}

/*
 * Bundle multiple PHP files into a single file, in the right order.
 * Removes the outside <?php...?> markers, as well as
 * - include statements
 * - declare statements
 * - empty lines
 */
function bundlePHP(srcdir: string, entrypoint: string, version: string): string
{
    console.log('Bundling files for '+srcdir+'/'+entrypoint+'...');
    let incPattern: RegExp = /include(_once|)\(["']([^"']+.php)["']\);/g;

    let result: string = "const VERSION = "+JSON.stringify(version)+";\n";
    let inputFiles: { [fname:string]: { content: string, deps: string[], done: boolean } } = {};
    let fileList: string[] = [ entrypoint ];
    for(let i = 0; i < fileList.length; i++) {
        let relPath: string = fileList[i];
        let fullPath: string = srcdir+'/'+relPath;
        if(!fs.existsSync(fullPath)) {
            console.log('Unresolved dependency: '+relPath);
            continue;
        }
        console.log('Processing: '+relPath);
        let dirpath: string = relPath.replace(/[^\/]+$/g, '');
        let content: string = fs.readFileSync(fullPath, 'utf-8');
        let deps: string[] = [];
        let execarr: RegExpExecArray | null;
        while ((execarr = incPattern.exec(content)) !== null) {
            let included: string = dirpath+execarr[2];
            deps.push(included);
            if(!inputFiles[included]) {
                fileList.push(included);
            }
        }
        inputFiles[relPath]= { content: content, deps: deps, done: false };
    }
    let completelyDone: boolean = false;
    while(!completelyDone) {
        let somethingDone: boolean = false;
        completelyDone = true;
        for(let relPath in inputFiles) {
            let file = inputFiles[relPath];
            if(file.done) continue;
            file.done = true;
            for(let dep of file.deps) {
                if(!inputFiles[dep].done) {
                    file.done = false;
                    break;
                }
            }
            if(file.done) {
                result += file.content
                    .replace(/^\s*(include|include_once|declare)\([^)]+\);\s*[\r\n]/gm, '')
                    .replace(/\/\*(.|\r|\n)*?\*\//g, '')
                    .replace(/^<\?php\s+/, '')
                    .replace(/\?>\s*$/, '')
                    .replace(/^\s*[\r\n]/gm, '') + '\n';
                somethingDone = true;
            } else {
                completelyDone = false;
            }
        }
        if(!somethingDone) {
            console.log('Cannot bundle file, circular dependency remaining for:');
            for(let relPath in inputFiles) {
                let file = inputFiles[relPath];
                if(!file.done) {
                    console.log('- '+relPath);
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
function createPHPinstaller(srcdir: string, distdir: string, instdir: string, bundleName: string, version: string, banner: string)
{
    let distpath: string = distdir+'/'+bundleName;
    let instpath: string = instdir+'/'+bundleName;
    let initFile: string = fs.readFileSync(srcdir+'/vhub4web-init.php', 'utf-8')
        .replace(/include_once\([^)]+\);/m, `include_once(__DIR__.'/${bundleName}-php'.PHP_MAJOR_VERSION.'.php');`);
    let bundle: string = '<?php /* '+banner+' - www.yoctopuce.com */\ndeclare(strict_types=1);\n' +
        bundlePHP(srcdir, 'VHubServer.php', version);
    let installerFile: string = '<?php /* '+banner+' Installer - www.yoctopuce.com */\ndeclare(strict_types=1);\n'+
        '/* This data blob is generated by build.ts. It contains VirtualHub-4web php code, gzipped */\n';
    let gzbundle: Uint8Array = <Uint8Array>Pako.Pako_Deflate.gzip(bundle, {level: 9});
    installerFile += "$phpCode = 'data:text/plain;base64,"+Buffer.from(gzbundle).toString('base64')+"';\n";
    let yfsImage: Uint8Array = fs.readFileSync(srcdir+'/YFSImg.yfs');
    installerFile += "$yfsImage = 'data:text/plain;base64,"+Buffer.from(yfsImage).toString('base64')+"';\n";
    installerFile += "$initCode = 'data:text/plain;base64,"+Buffer.from(initFile).toString('base64')+"';\n";
    installerFile += bundlePHP(srcdir, 'Installer.php', version);

    // Create distribution files
    fs.mkdirSync(distpath.slice(0,distpath.lastIndexOf('/')),{ recursive: true });
    fs.mkdirSync(instpath.slice(0,instpath.lastIndexOf('/')),{ recursive: true });
    fs.writeFileSync(distdir + '/YFSImg.yfs', yfsImage);
    fs.writeFileSync(distpath + '-php8.php', bundle, 'utf-8');
    fs.writeFileSync(distpath + '-php7.php', downgradePHP(bundle), 'utf-8');
    fs.writeFileSync(distpath + '-init.php', initFile, 'utf-8');
    fs.writeFileSync(instpath + '-installer.php', installerFile);
}

function setupPHPtestEnv(installer: string, testdir: string)
{
    let prevInstallers = findFilesRecursively(testdir, /^vhub4web-installer.*\.php$/);
    for(let prevFile of prevInstallers) {
        fs.unlinkSync(prevFile);
    }
    fs.copyFile(installer, testdir+'/vhub4web-installer.php', ()=>{})
}

async function transpileTS(srcdir: string, objdir: string)
{
    const cwd: string = process.cwd().replace(/\\/g, '/');
    let options: ts.CompilerOptions = {
        strict: true,
        allowSyntheticDefaultImports: true,
        esModuleInterop: true,
        skipLibCheck: true,
        target: ts.ScriptTarget.ES2017,
        "outDir": objdir
    };
    console.log('Transpiling '+srcdir+' TypeScript files...');
    let inputFiles: string[] = findFilesRecursively(srcdir, /\.ts$/);
    let program = ts.createProgram(inputFiles, options);
    let emitResult = program.emit();
    let allDiagnostics = ts.getPreEmitDiagnostics(program).concat(emitResult.diagnostics);
    console.log(allDiagnostics.length+' messages generated by TypeScript compiler');
    allDiagnostics.forEach(diagnostic => {
        if (diagnostic.file) {
            let { line, character } = diagnostic.file.getLineAndCharacterOfPosition(diagnostic.start!);
            let message = ts.flattenDiagnosticMessageText(diagnostic.messageText, "\n");
            console.log(`${diagnostic.file.fileName} (${line + 1},${character + 1}): ${message}`);
        } else {
            console.log(ts.flattenDiagnosticMessageText(diagnostic.messageText, "\n"));
        }
    });
}

async function bundleTS(objdir: string, distdir: string, bundleName: string, banner: string)
{
    await esbuild.build({
        entryPoints: [objdir+'/index.js'],
        bundle: true,
        target: 'es2017',
        format: 'esm',
        sourcemap: true,
        banner: banner,
        outfile: distdir+'/'+bundleName+'.js',
    });
    await esbuild.build({
        entryPoints: [objdir+'/index.js'],
        bundle: true,
        target: 'es2017',
        format: 'esm',
        sourcemap: false,
        minify: true,
        banner: banner,
        outfile: distdir+'/'+bundleName+'.min.js',
    });
}

async function createJSinstaller(srcdir: string, objdir: string, distdir: string, bundleName: string, banner: string)
{
    await transpileTS(srcdir, objdir);
    await bundleTS(objdir, distdir, bundleName, '/* '+banner+' */');
}

let args: string[] = process.argv.slice(2);
if(args.length == 0) {
    console.log("command expected")
} else {
    let json: { version: string } = JSON.parse(fs.readFileSync('package.json', 'utf8'));
    let banner: string = 'VirtualHub-4web (version '+json.version+')';
    // Create data directory used for debugging
    if(!fs.existsSync('data')) {
        fs.mkdirSync('data');
    }
    switch(args[0]) {
        case "newbuild":
            setVersion(args[1]);
            break;
        case "build_php":
            console.log('Building version ' + json.version + ' for PHP');
            createPHPinstaller('PHP-Version/src', 'PHP-Version/dist', 'PHP-Version/installer', 'vhub4web', json.version, banner);
            setupPHPtestEnv('PHP-Version/installer/vhub4web-installer.php', 'PHP-Version/www/VirtualHub-4web');
            break;
        case "build_nodejs":
            console.log('Sorry, Node.js version is not yet available...');
            //-- console.log('Building version ' + json.version + ' for Node.js');
            //-- createJSinstaller('NodeJS-Version/src', 'NodeJS-Version/obj', 'NodeJS-Version/dist', 'vhub4web', banner);
            break;
    }
}

