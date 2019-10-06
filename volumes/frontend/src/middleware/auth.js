export default async function({
    store,
    redirect,
    route
}) {
    const redirectPath = route.path.indexOf('dashboard') !== false ? '/dashboard/login' : '/login';
    if (!store.getters['account/authenticated']) {
        return redirect(redirectPath);
    }
}
