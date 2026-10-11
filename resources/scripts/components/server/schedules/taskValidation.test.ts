import { expect, it } from 'vitest';
import { taskSchema } from './taskValidation';

it('requires payloads for command and power tasks while allowing empty backup payloads', () => {
    for (const action of ['command', 'power']) {
        expect(taskSchema.isValidSync({ action, payload: '', timeOffset: '0' })).toBe(false);
        expect(taskSchema.isValidSync({ action, payload: 'start', timeOffset: '0' })).toBe(true);
    }
    expect(taskSchema.isValidSync({ action: 'backup', payload: '', timeOffset: '0' })).toBe(true);
});

it('rejects unsupported actions and invalid time offsets', () => {
    expect(taskSchema.isValidSync({ action: 'invalid', payload: 'value', timeOffset: '0' })).toBe(false);
    for (const timeOffset of ['-1', '901', 'abc']) {
        expect(taskSchema.isValidSync({ action: 'backup', timeOffset })).toBe(false);
    }
});
