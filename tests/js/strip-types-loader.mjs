import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { fileURLToPath, pathToFileURL } from 'node:url';

const ts = createRequire(import.meta.url)('typescript');

/**
 * CI runs Node 20, which rejects --experimental-strip-types.
 * Transpile the filter script with the TypeScript already installed.
 */
export async function resolve(specifier, context, nextResolve) {
    if (specifier.endsWith('.ts')) {
        const parent = context.parentURL ?? pathToFileURL(`${process.cwd()}/`).href;

        return {
            format: 'module',
            shortCircuit: true,
            url: new URL(specifier, parent).href,
        };
    }

    return nextResolve(specifier, context);
}

export async function load(url, context, nextLoad) {
    if (!url.startsWith('file:') || !url.endsWith('.ts')) {
        return nextLoad(url, context);
    }

    const filename = fileURLToPath(url);
    const source = readFileSync(filename, 'utf8');
    const { outputText } = ts.transpileModule(source, {
        compilerOptions: {
            module: ts.ModuleKind.ESNext,
            target: ts.ScriptTarget.ES2022,
        },
        fileName: filename,
    });

    return {
        format: 'module',
        shortCircuit: true,
        source: outputText,
    };
}
