document.addEventListener('DOMContentLoaded', function () {
    // 1. 要素の取得
    // input[type="file"]（ファイル選択ボタン）を取得
    const fileInput = document.querySelector('input[type="file"]');
    
    // 画像を表示させる外枠（「画像を選択する」の文字が入っているエリア）を取得
    // もしHTML側に id="image-preview-area" などがあればそれに書き換えてください
    const previewArea = fileInput.closest('div');

    // 2. ファイル選択時のイベント
    fileInput.addEventListener('change', function (e) {
        const file = e.target.files[0]; // 選択された最初のファイル

        // ファイルがない、または画像以外の場合は処理を終了
        if (!file || !file.type.match('image.*')) {
            return;
        }

        // 3. 画像の読み込み処理
        const reader = new FileReader();

        reader.onload = function (e) {
            // すでに表示されているプレビュー画像があれば削除
            const existingImg = previewArea.querySelector('img');
            if (existingImg) {
                existingImg.remove();
            }

            // 新しくimgタグを作成
            const img = document.createElement('img');
            img.src = e.target.result; // 読み込んだ画像データ

            // 見栄えの調整（枠いっぱいに表示）
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'contain';
            img.style.position = 'absolute';
            img.style.top = '0';
            img.style.left = '0';

            // エリア内の既存テキスト（「画像を選択する」など）を隠すために
            // エリア内の要素を整理して画像を追加
            previewArea.style.position = 'relative'; // 画像を重ねるための準備
            previewArea.appendChild(img);
        }

        // 実際の読み込み開始
        reader.readAsDataURL(file);
    });
});
