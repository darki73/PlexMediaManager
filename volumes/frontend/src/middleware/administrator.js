import forEach from 'lodash/forEach';

export default async function({
    store,
    redirect
}) {
    const user = store.getters['account/user'];
    let isAdministrator = false;

    if (!user.hasOwnProperty('roles')) {
        return redirect('/');
    }

    forEach(user.roles, (role, index) => {
        if (role.name.indexOf('administrator') !== false) {
            isAdministrator = true;
        }
    });

    if (!isAdministrator) {
        return redirect('/');
    }
}
