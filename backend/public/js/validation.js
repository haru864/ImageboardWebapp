function validateSubject() {
    const MAX_SUBJECT_LENGTH = 50;
    let subject = document.getElementById('subject').value;
    if (subject.length > MAX_SUBJECT_LENGTH) {
        throw new Error(`主題は${MAX_SUBJECT_LENGTH}文字以内にしてください。`);
    }
    return;
}

function validateContent() {
    const MAX_MYSQL_TEXT_BYTES = 65535;
    let content = document.getElementById('content').value;
    let contentByteSize = new Blob([content]).size;
    if (contentByteSize > MAX_MYSQL_TEXT_BYTES) {
        throw new Error(`本文のサイズが大きすぎます。${MAX_MYSQL_TEXT_BYTES}バイト以内にしてください。`);
    }
    return;
}

function validateFile() {
    const VALID_FILES = ['jpg', 'jpeg', 'png', 'gif'];
    let fileInput = document.getElementById('image');
    let file = fileInput.files[0];
    let fileName = file.name;
    let extension = fileName.split('.').pop().toLowerCase();
    if (!VALID_FILES.includes(extension)) {
        throw new Error(`${VALID_FILES.join(',')}のみアップロードできます。`);
    }
}
