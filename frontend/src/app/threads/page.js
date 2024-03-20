"use client"

import { validateSubject, validateContent, validateFile } from '../validation'
import React, { useEffect, useState } from 'react';
import { Box, Button, TextField, Typography, Card, CardContent, CircularProgress } from '@mui/material';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';

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

    const response = await fetch('http://imageboard.test.com/api/threads', {
      method: 'POST',
      body: formData,
    });
    if (!response.ok) {
      console.error('リクエスト失敗:', response);
      return;
    }

    console.log('リクエスト成功:', response);
    const data = await response.json();
    const threadId = data.id;
    window.location.href = `http://imageboard.test.com/replies?id=${threadId}`;

  } catch (error) {
    console.error('エラーが発生しました:', error);
    alert(error);
  }
}

const renderImage = (fileName) => {
  if (!fileName) {
    return null;
  }
  const imageUrl = `http://imageboard.test.com/images/thumbnails/${fileName}`;
  return <img src={imageUrl} alt="" style={{ maxWidth: '100%', height: 'auto' }} />;
};

const displayNumOfReplies = (repliesArr) => {
  const numOfRepies = repliesArr.length;
  var message = null;
  if (numOfRepies === 0) {
    message = 'まだリプライがありません。';
  } else {
    message = `最新のリプライ${numOfRepies}件を以下に表示します。`;
  }
  return <Typography variant="body2" sx={{ marginTop: 2, marginLeft: 4 }}>{message}</Typography>;
}

function ThreadsDisplay() {
  const [threads, setThreads] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch('http://imageboard.test.com/api/threads');
      const data = await response.json();
      setThreads(data.threads);
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
      <Box sx={{ margin: 2 }} label="thread-list">
        {threads.map((thread) => (
          <a href={`/replies?id=${thread.postId}`} key={thread.postId} style={{ textDecoration: 'none' }}>
            <Card key={thread.postId} sx={{ marginBottom: 2 }}>
              <CardContent>
                <Typography variant="h5" sx={{ marginBottom: 2 }}>{thread.subject}</Typography>
                <Box display="flex" flexDirection="row" alignItems="center">
                  {renderImage(thread.imageFileName)}
                  <Typography variant="body1" sx={{ marginLeft: 2 }}>{thread.content}</Typography>
                </Box>
                {displayNumOfReplies(thread.replies)}
                {thread.replies.map((reply) => (
                  <Card key={reply.postId} sx={{ marginTop: 2, marginLeft: 4 }}>
                    <CardContent>
                      <Box display="flex" flexDirection="row" alignItems="center">
                        {renderImage(reply.imageFileName)}
                        <Typography variant="body2" sx={{ marginLeft: 2 }}>{reply.content}</Typography>
                      </Box>
                    </CardContent>
                  </Card>
                ))}
              </CardContent>
            </Card>
          </a>
        ))}
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
        <Typography variant="h4" sx={{ marginBottom: 1 }}>スレッドを作成する</Typography>
        <TextField
          required
          id="subject"
          label="Subject（50文字まで）"
          defaultValue=""
          variant="outlined"
          sx={{ width: '50ch', marginBottom: 1 }}
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
        <Button variant="contained" onClick={handleSubmit}>create</Button>
      </Card >
    </Box>
  );
}

export default ThreadsDisplay;
