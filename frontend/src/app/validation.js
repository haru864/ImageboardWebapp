export const validateSubject = (subject) => {
    if (typeof subject !== 'string') {
        throw new Error(`'subject'には文字列を指定してください。`);
    }
    const MAX_SUBJECT_LENGTH = 50;
    if (subject.length > MAX_SUBJECT_LENGTH) {
        throw new Error(`主題は${MAX_SUBJECT_LENGTH}文字以内にしてください。`);
    }
    return;
}

export const validateContent = (content) => {
    if (typeof content !== 'string') {
        throw new Error(`'content'には文字列を指定してください。`);
    }
    const MAX_MYSQL_TEXT_BYTES = 65535;
    let contentByteSize = new Blob([content]).size;
    if (contentByteSize > MAX_MYSQL_TEXT_BYTES) {
        throw new Error(`本文のサイズが大きすぎます。${MAX_MYSQL_TEXT_BYTES}バイト以内にしてください。`);
    }
    return;
}

export const validateFile = (file) => {
    if (!(file instanceof File)) {
        console.log(`'file'にはFileオブジェクトを指定してください。`);
    }
    const VALID_FILES = ['jpg', 'jpeg', 'png', 'gif'];
    let fileName = file.name;
    let extension = fileName.split('.').pop().toLowerCase();
    if (!VALID_FILES.includes(extension)) {
        throw new Error(`'${VALID_FILES.join(',')}'のみアップロードできます。`);
    }
}
