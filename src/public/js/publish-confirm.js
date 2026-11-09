// 日報を「公開」で保存するとき（作成で公開を選んだとき、編集で非公開→公開に変えたとき）だけ確認ダイアログを出す。
// JavaScript が無効な環境では、ダイアログを出さずにそのまま送信される。
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-publish-confirm]');
    const dialog = document.getElementById('publish-confirm-dialog');

    if (! form || ! dialog || typeof dialog.showModal !== 'function') {
        return;
    }

    const wasPublic = form.dataset.wasPublic === '1';
    let confirmed = false;

    const selectedVisibility = () => {
        const field = form.querySelector('[name="is_public"]:checked') ?? form.querySelector('select[name="is_public"]');

        return field ? field.value : '0';
    };

    form.addEventListener('submit', (event) => {
        if (confirmed || wasPublic || selectedVisibility() !== '1') {
            return;
        }

        event.preventDefault();
        dialog.showModal();
    });

    // 戻る: ダイアログを閉じて入力画面に戻る（入力は残り、保存はしない）
    dialog.querySelector('[data-publish-cancel]').addEventListener('click', () => dialog.close());

    // 公開する: そのまま保存する
    dialog.querySelector('[data-publish-accept]').addEventListener('click', () => {
        confirmed = true;
        dialog.close();
        form.requestSubmit();
    });
});
