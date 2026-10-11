import { renderToStaticMarkup } from 'react-dom/server';
import { describe, expect, it, vi } from 'vitest';
import Avatar, { ANIMATED_AVATAR_STYLES, AVATAR_STYLES, avatarUrl } from './Avatar';

const user = vi.hoisted(() => ({
    email: 'john@example.com',
    uuid: '00000000-0000-4000-8000-000000000001',
    name_first: 'John',
    name_last: 'Doe',
    avatarStyle: 'initials',
    avatarAnimated: true,
}));

vi.mock('easy-peasy', () => ({
    useStoreState: (selector: (state: unknown) => unknown) => selector({ user: { data: user } }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));

describe('initials avatars', () => {
    it.each([
        ['John Doe', 'JD'],
        ['John', 'JO'],
        ['John Michael Doe', 'JO'],
        ['John Michael James Doe', 'JO'],
        ['  john \t doe  ', 'JD'],
        ['Élodie Öberg', 'ÉÖ'],
        ['李 王', '李王'],
        ['J', 'J'],
    ])('uses the expected seed for %s', (name, seed) => {
        const url = new URL(avatarUrl({ ...user, name }));
        expect(url.pathname).toBe('/10.x/initials/svg');
        expect(url.searchParams.get('seed')).toBe(seed);
        expect(url.searchParams.has('animationVariant')).toBe(false);
    });

    it.each([undefined, '', ' \t '])('keeps the UUID fallback when the name is %s', (name) => {
        expect(new URL(avatarUrl({ ...user, name })).searchParams.get('seed')).toBe(user.uuid);
    });

    it.each(AVATAR_STYLES.filter((style) => !['initials', 'gravatar'].includes(style)))(
        'keeps the UUID seed and animation preference for %s',
        (avatarStyle) => {
            const url = new URL(avatarUrl({ ...user, name: 'John Doe', avatarStyle }));
            expect(url.searchParams.get('seed')).toBe(user.uuid);
            expect(url.searchParams.get('animationVariant')).toBe(
                ANIMATED_AVATAR_STYLES.includes(avatarStyle) ? 'medium' : null,
            );
        },
    );

    it('keeps Gravatar and invalid-style fallback unchanged', () => {
        const expected = avatarUrl({ ...user, avatarStyle: 'gravatar' });
        expect(avatarUrl({ ...user, name: 'Jane Smith', avatarStyle: 'gravatar' })).toBe(expected);
        expect(avatarUrl({ ...user, name: 'Jane Smith', avatarStyle: 'invalid' })).toBe(expected);
    });

    it('uses the current account name for the sidebar and style preview', () => {
        expect(renderToStaticMarkup(<Avatar />)).toContain('seed=JD');
        expect(renderToStaticMarkup(<Avatar email={user.email} uuid={user.uuid} avatarStyle='initials' />)).toContain(
            'seed=JD',
        );
    });

    it('uses an explicit name for another user and never substitutes the viewer name', () => {
        expect(renderToStaticMarkup(<Avatar uuid='another-user' name='Jane Smith' />)).toContain('seed=JS');
        expect(renderToStaticMarkup(<Avatar uuid='another-user' />)).toContain('seed=another-user');
        expect(renderToStaticMarkup(<Avatar email='jane@example.com' uuid='another-user' />)).not.toContain('seed=JD');
    });

    it('preserves the actor image provided by the activity API', () => {
        const src = avatarUrl({ uuid: 'another-user', name: 'Jane Smith', avatarStyle: 'initials' });
        expect(renderToStaticMarkup(<Avatar uuid='another-user' src={src} />)).toContain(src);
    });
});
