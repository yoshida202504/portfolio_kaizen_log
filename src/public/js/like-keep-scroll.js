// いいね・いいねの取り消しのあと、押す前と同じスクロール位置に戻す。
// サーバーは #like-section 付きで日報の詳細に戻すため、そのままだと
// いいねボタンが画面の上端に来るようにスクロールしてしまう。
// JavaScript が動かないときは、今までどおり #like-section の位置に移る。
(function () {
    var KEY = 'kaizen-log:like-scroll';

    function read() {
        try {
            var raw = window.sessionStorage.getItem(KEY);
            window.sessionStorage.removeItem(KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    var saved = read();

    if (saved && saved.path === window.location.pathname && window.location.hash === '#like-section') {
        var restore = function () {
            window.scrollTo(0, saved.y);
        };
        restore();
        document.addEventListener('DOMContentLoaded', restore);
        window.addEventListener('load', restore);
    }

    document.addEventListener('submit', function (event) {
        if (!event.target.closest('#like-section')) {
            return;
        }
        try {
            window.sessionStorage.setItem(KEY, JSON.stringify({
                path: window.location.pathname,
                y: window.scrollY,
            }));
        } catch (e) {
            // 保存できないときは、今までどおりの動きにする
        }
    });
})();
