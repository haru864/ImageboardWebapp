"use client"

import { validateSubject, validateContent, validateFile } from '../validation'
import React from 'react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import Typography from '@mui/material/Typography'

async function handleSubmit() {
  try {
    const subject = document.getElementById('subject').value;
    const content = document.getElementById('content').value;
    const fileInput = document.querySelector('input[type="file"]');
    const file = fileInput.files[0];

    validateSubject(subject);
    validateContent(content);
    validateFile(file);

    const formData = new FormData();
    formData.append('subject', subject);
    formData.append('content', content);
    if (file) {
      formData.append('image', file);
    }

    const response = await fetch('http://localhost:8000/api/threads', {
      method: 'POST',
      body: formData,
    });
    if (response.ok) {
      console.log('リクエスト成功:', response);
    } else {
      console.error('リクエスト失敗:', response);
    }

  } catch (error) {
    console.error('エラーが発生しました:', error);
  }
}

function MyApp() {
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
      <Typography variant="h4">スレッドを作成する</Typography>
      <TextField
        required
        id="subject"
        label="Subject（50文字まで）"
        defaultValue=""
        variant="outlined"
        sx={{ width: '50ch' }}
      />
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

export default MyApp;
