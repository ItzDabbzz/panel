import { boolean, number, object, string } from 'yup';

export const taskSchema = object().shape({
    action: string().required().oneOf(['command', 'power', 'backup']),
    payload: string().when('action', {
        is: (v: string) => v !== 'backup',
        then: (schema) => schema.required('A task payload must be provided.'),
        otherwise: (schema) => schema,
    }),
    continueOnFailure: boolean(),
    timeOffset: number()
        .typeError('The time offset must be a valid number between 0 and 900.')
        .required('A time offset value must be provided.')
        .min(0, 'The time offset must be at least 0 seconds.')
        .max(900, 'The time offset must be less than 900 seconds.'),
});
