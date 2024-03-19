"use client"

import { validateContent, validateFile } from '../validation'
import React from 'react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import Typography from '@mui/material/Typography'

async function handleSubmit() {
    try {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const id = urlParams.get('id');
        const content = document.getElementById('content').value;
        const fileInput = document.querySelector('input[type="file"]');
        const file = fileInput.files[0];

        validateContent(content);
        validateFile(file);

        const formData = new FormData();
        formData.append('id', id);
        formData.append('content', content);
        if (file) {
            formData.append('image', file);
        }

        const response = await fetch('http://localhost:8000/api/replies', {
            method: 'POST',
            body: formData,
        });
        if (!response.ok) {
            console.error('リクエスト失敗:', response);
            return;
        }
        console.log('リクエスト成功:', response);

    } catch (error) {
        console.error('エラーが発生しました:', error);
        alert(error);
    }
}

function Replies() {
    return (
        <Box
            sx={{
                bgcolor: 'background.paper',
                height: '100vh',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '16px',
            }}
        >
            <Typography variant="h4">リプライ</Typography>
            <TextField
                required
                id="content"
                label="Content（300文字まで）"
                defaultValue=""
                variant="outlined"
                multiline
                rows={3}
                rowsmax={6}
                sx={{ width: '50ch' }}
            />
            <Button
                component="label"
                variant="contained"
                startIcon={<CloudUploadIcon />}
            >
                Upload Image
                <input type="file" hidden />
            </Button>
            <Button variant="contained" onClick={handleSubmit}>create</Button>
        </Box>
    );
}

export default Replies;
