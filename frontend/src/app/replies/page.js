"use client"

import { validateContent, validateFile } from '../validation'
import React, { useState, useEffect } from 'react';
import { Box, Button, TextField, Typography, Card, CardContent, CircularProgress } from '@mui/material';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import Link from 'next/link';

async function handleSubmit(threadId) {

    const content = document.getElementById('content').value;
    const fileInput = document.querySelector('input[type="file"]');
    const file = fileInput.files[0];

    let contentError = null;
    let fileError = null;
    try {
        validateContent(content);
    } catch (error) {
        console.error('Content validation failed:', error);
        contentError = error;
    }
    try {
        validateFile(file);
    } catch (error) {
        console.error('File validation failed:', error);
        fileError = error;
    }
    if (contentError && fileError) {
        console.error('Both content and file validation failed');
        alert(`本文または画像ファイルの選択が必要です。`);
        return;
    }

    try {
        const formData = new FormData();
        formData.append('id', threadId);
        formData.append('content', content);
        if (file) {
            formData.append('image', file);
        }

        const response = await fetch(`${process.env.apiDomain}/api/replies`, {
            method: 'POST',
            body: formData,
        });
        if (!response.ok) {
            console.error('リクエスト失敗:', response);
            throw new Error('サーバーとの接続でエラーが発生しました。');
        }
        console.log('リクエスト成功:', response);
        location.reload()

    } catch (error) {
        console.error('エラーが発生しました:', error);
        alert(error);
    }
}

const renderImage = (fileName) => {
    if (!fileName) {
        return null;
    }
    const thumbnailUrl = `${process.env.apiDomain}/images/thumbnails/${fileName}`;
    const imageUrl = `${process.env.apiDomain}/images/uploads/${fileName}`;
    return (
        <Link href={imageUrl}>
            <img src={thumbnailUrl} alt="" style={{ maxWidth: '100%', height: 'auto' }} />
        </Link>
    );
};

function Replies() {
    const [threadId, setThreadId] = useState(null);
    const [thread, setThread] = useState(null);
    const [replies, setReplies] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const id = urlParams.get('id');
        setThreadId(id);
        const fetchData = async () => {
            const response = await fetch(`${process.env.apiDomain}/api/replies?id=${id}`);
            const data = await response.json();
            setThread(data.thread);
            setReplies(data.replies);
            setIsLoading(false);
        };
        fetchData();
    }, []);

    if (isLoading) {
        return (
            <Box display="flex" justifyContent="center" alignItems="center" height="100vh">
                <CircularProgress />
            </Box>
        );
    }

    return (
        <Box
            component="main"
            sx={{
                backgroundColor: 'lightblue',
                overflow: 'auto',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                padding: '20px 0'
            }}
        >
            <Box>
                <Link href={`${process.env.frontDomain}/threads`}>
                    <Button variant="contained" color="primary" sx={{ marginLeft: 2 }}>
                        スレッド一覧へ
                    </Button>
                </Link>
            </Box>
            <Typography variant="subtitle2" sx={{ marginTop: 2, marginLeft: 2 }}>
                画像をクリックするとフルサイズで表示します。
            </Typography>
            <Box sx={{ margin: 2 }} label="reply-list">
                <Card key={thread.postId} sx={{ marginBottom: 2 }}>
                    <CardContent>
                        <Typography variant="h5" sx={{ marginBottom: 2 }}>{thread.subject}</Typography>
                        <Box display="flex" flexDirection="row" alignItems="center">
                            {renderImage(thread.imageFileName)}
                            <Typography
                                variant="body1"
                                sx={{
                                    marginLeft: 2,
                                    flexGrow: 1,
                                    minWidth: 0,
                                    overflow: 'hidden',
                                    textOverflow: 'ellipsis',
                                    wordWrap: 'break-word'
                                }}
                            >
                                {thread.content}
                            </Typography>
                        </Box>
                        {replies.map((reply) => (
                            <Card key={reply.postId} sx={{ marginTop: 2, marginLeft: 4 }}>
                                <CardContent>
                                    <Box display="flex" flexDirection="row" alignItems="center">
                                        {renderImage(reply.imageFileName)}
                                        <Typography
                                            variant="body2"
                                            sx={{
                                                marginLeft: 2,
                                                flexGrow: 1,
                                                minWidth: 0,
                                                overflow: 'hidden',
                                                textOverflow: 'ellipsis',
                                                wordWrap: 'break-word'
                                            }}
                                        >
                                            {reply.content}
                                        </Typography>
                                    </Box>
                                </CardContent>
                            </Card>
                        ))}
                    </CardContent>
                </Card>
            </Box>
            <Card
                sx={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'flex-start',
                    padding: '20px',
                    margin: 2,
                    marginTop: -2
                }}
                label="input"
            >
                <Typography variant="h4" sx={{ marginBottom: 1 }}>リプライ</Typography>
                <TextField
                    required
                    id="content"
                    label="Content（300文字まで）"
                    defaultValue=""
                    variant="outlined"
                    multiline
                    rows={3}
                    rowsmax={6}
                    sx={{ width: '50ch', marginBottom: 1 }}
                />
                <Button
                    component="label"
                    variant="contained"
                    startIcon={<CloudUploadIcon />}
                    sx={{ marginBottom: 1 }}
                >
                    Upload Image
                    <input type="file" hidden />
                </Button>
                <Button variant="contained" onClick={() => { handleSubmit(threadId) }}>create</Button>
            </Card>
        </Box>
    );
}

export default Replies;
