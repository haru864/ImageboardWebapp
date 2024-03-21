export const validateSubject = (subject) => {
    if (typeof subject !== 'string') {
        throw new Error(`'subject'には文字列を指定してください。`);
    }
    const MIN_SUBJECT_LENGTH = 1;
    const MAX_SUBJECT_LENGTH = 50;
    if (subject.length < MIN_SUBJECT_LENGTH || subject.length > MAX_SUBJECT_LENGTH) {
        throw new Error(`主題は${MIN_SUBJECT_LENGTH}文字以上、${MAX_SUBJECT_LENGTH}文字以内にしてください。`);
    }
    return;
}

export const validateContent = (content) => {
    if (typeof content !== 'string') {
        throw new Error(`'content'には文字列を指定してください。`);
    }
    const MIN_CONTENT_LENGTH = 1;
    const MAX_CONTENT_LENGTH = 300;
    if (content.length < MIN_CONTENT_LENGTH || content.length > MAX_CONTENT_LENGTH) {
        throw new Error(`本文は${MIN_CONTENT_LENGTH}文字以上、${MAX_CONTENT_LENGTH}文字以内にしてください。`);
    }
    return;
}

export const validateFile = (file) => {
    if (!(file instanceof File)) {
        throw new Error(`アップロードする画像ファイルを選択してください。`);
    }
    const VALID_FILES = ['jpg', 'jpeg', 'png', 'gif'];
    let fileName = file.name;
    let extension = fileName.split('.').pop().toLowerCase();
    if (!VALID_FILES.includes(extension)) {
        throw new Error(`'${VALID_FILES.join(',')}'のみアップロードできます。`);
    }
}
