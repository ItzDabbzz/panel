import type { TFunction } from 'i18next';
import { boolean, object, string } from 'yup';

export const createImportSchema = (t: TFunction<'server/databases'>) =>
    object().shape({
        wipe: boolean(),
        remote: boolean(),
        host: string().when('remote', {
            is: true,
            then: (schema) =>
                schema
                    .required(t('import-remote-host-required'))
                    .matches(/^[\w\-.:[\]]+$/, t('import-remote-host-invalid')),
        }),
        port: string().when('remote', {
            is: true,
            then: (schema) =>
                schema
                    .required(t('import-remote-port-invalid'))
                    .test('port', t('import-remote-port-invalid'), (value) => {
                        return /^\d{1,5}$/.test(value || '') && Number(value) >= 1 && Number(value) <= 65535;
                    }),
        }),
        database: string().when('remote', {
            is: true,
            then: (schema) => schema.required(t('import-remote-database-required')),
        }),
        username: string().when('remote', {
            is: true,
            then: (schema) => schema.required(t('import-remote-username-required')),
        }),
        password: string(),
    });
