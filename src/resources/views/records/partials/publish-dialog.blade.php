{{-- 「公開」で保存するときだけ出す確認ダイアログ（public/js/publish-confirm.js が開く） --}}
<dialog id="publish-confirm-dialog" class="confirm-dialog" aria-labelledby="publish-confirm-title">
    <h2 id="publish-confirm-title">公開の確認</h2>
    <p>この日報を【公開用の日報】にします。他のユーザーが閲覧できるようになりますが、よろしいでしょうか？</p>
    <div class="confirm-dialog-actions">
        <button class="button-secondary" type="button" data-publish-cancel>戻る</button>
        <button class="button" type="button" data-publish-accept>公開する</button>
    </div>
</dialog>
<script src="{{ asset('js/publish-confirm.js') }}" defer></script>
