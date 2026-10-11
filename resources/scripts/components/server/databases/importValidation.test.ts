import type { TFunction } from 'i18next';
import { describe, expect, it } from 'vitest';
import { createImportSchema } from './importValidation';

const schema = createImportSchema(((key: string) => key) as TFunction<'server/databases'>);
const remote = { remote: true, host: 'db.example.com', port: '3306', database: 'game', username: 'user' };

describe('database import validation', () => {
    it('allows file imports without remote credentials', () => {
        expect(schema.isValidSync({ remote: false, host: '', port: '', database: '', username: '' })).toBe(true);
    });

    it('requires each remote connection field', () => {
        expect(schema.isValidSync(remote)).toBe(true);
        for (const field of ['host', 'port', 'database', 'username']) {
            expect(schema.isValidSync({ ...remote, [field]: '' })).toBe(false);
        }
    });

    it('checks remote host and port boundaries', () => {
        expect(schema.isValidSync({ ...remote, host: 'host with spaces' })).toBe(false);
        for (const port of ['0', '65536', 'abc']) expect(schema.isValidSync({ ...remote, port })).toBe(false);
        for (const port of ['1', '65535']) expect(schema.isValidSync({ ...remote, port })).toBe(true);
    });
});
