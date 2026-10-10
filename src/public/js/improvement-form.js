document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-improvement-form]');

    if (! form) {
        return;
    }

    const executedGroup = form.querySelector('[data-improvement-group="executed"]');
    const notExecutedGroup = form.querySelector('[data-improvement-group="not-executed"]');
    const otherReasonGroup = form.querySelector('[data-improvement-group="other-reason"]');
    const reasonSelect = form.querySelector('#not_executed_reason');

    // JavaScriptが無効な環境では全項目を表示したままにし、選択された評価に応じて必要な欄だけを表示する
    const update = () => {
        const evaluation = form.querySelector('input[name="evaluation"]:checked')?.value;

        executedGroup.hidden = ! ['A', 'B', 'C'].includes(evaluation);
        notExecutedGroup.hidden = evaluation !== 'D';
        otherReasonGroup.hidden = reasonSelect.value !== 'other';
    };

    form.querySelectorAll('input[name="evaluation"]').forEach((radio) => radio.addEventListener('change', update));
    reasonSelect.addEventListener('change', update);
    update();
});
