import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import { createHash } from 'node:crypto';
import { spawnSync } from 'node:child_process';

export default async function globalSetup() {
    const root = process.cwd();
    const connection = process.env.PORTGEN_E2E_DB_CONNECTION || 'sqlite';
    let database;
    const env = { ...process.env, APP_ENV: 'local', DB_CONNECTION: connection, DB_URL: '' };

    if (connection === 'sqlite') {
        const workspaceKey = createHash('sha256').update(root).digest('hex').slice(0, 12);
        const storage = os.tmpdir();
        database = path.resolve(storage, `portgen-browser-${workspaceKey}.sqlite`);
        if (!database.startsWith(`${storage}${path.sep}`) || !path.basename(database).startsWith('portgen-browser-')) {
            throw new Error('Refusing to use a browser test database outside its dedicated temporary file.');
        }
        fs.mkdirSync(storage, { recursive: true });
        fs.closeSync(fs.openSync(database, 'a'));
        env.DB_DATABASE = database;
        env.DB_HOST = '127.0.0.1';
        env.DB_PORT = '';
    } else {
        database = process.env.DB_DATABASE;
        const localHost = ['127.0.0.1', 'localhost'].includes(process.env.DB_HOST || '');
        const dedicatedDatabase = database === 'portgen_test' && process.env.DB_USERNAME === 'portgen_test';
        if (connection !== 'pgsql' || process.env.PORTGEN_E2E_TEST_DB !== '1' || !localHost || !dedicatedDatabase) {
            throw new Error('PostgreSQL browser tests require the explicitly named local portgen_test CI database.');
        }
    }

    if (String(env.DB_HOST || '').toLowerCase().includes('supabase') || String(env.DB_URL || '').toLowerCase().includes('supabase')) {
        throw new Error('Browser test database guard blocked a Supabase destination.');
    }

    const result = spawnSync('php', ['artisan', 'migrate:fresh', '--force'], {
        cwd: root,
        env,
        encoding: 'utf8',
        windowsHide: true,
    });
    if (result.stdout) process.stdout.write(result.stdout);
    if (result.status !== 0) {
        if (result.stderr) process.stderr.write(result.stderr);
        throw new Error(`Could not prepare the isolated ${connection} browser test database (${result.status ?? 'no exit code'}).`);
    }

    const fixtures = spawnSync('php', ['artisan', 'db:seed', '--class=SimpleTemplateDemoSeeder', '--force'], {
        cwd: root,
        env,
        encoding: 'utf8',
        windowsHide: true,
    });
    if (fixtures.stdout) process.stdout.write(fixtures.stdout);
    if (fixtures.status !== 0) {
        if (fixtures.stderr) process.stderr.write(fixtures.stderr);
        throw new Error(`Could not prepare the isolated ${connection} Simple screenshot fixtures (${fixtures.status ?? 'no exit code'}).`);
    }

    if (connection === 'sqlite' && !fs.existsSync(database)) throw new Error('Browser test database file was not created.');
}
