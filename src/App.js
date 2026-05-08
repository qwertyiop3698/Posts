import { useEffect, useState } from 'react';
import './App.scss';

const API_BASE = 'https://toma2025.dothome.co.kr/posts/backend';

const text = {
  add: '\uB4F1\uB85D',
  author: '\uC791\uC131\uC790',
  authFailed: '\uC778\uC99D \uC694\uCCAD \uC2E4\uD328: ',
  cancel: '\uCDE8\uC18C',
  comment: '\uB313\uAE00',
  comments: '\uB313\uAE00 \uBAA9\uB85D',
  confirmPassword: '\uBE44\uBC00\uBC88\uD638 \uD655\uC778',
  createAccount: '\uD68C\uC6D0\uAC00\uC785',
  createdAt: '\uC791\uC131\uC77C',
  delete: '\uC0AD\uC81C',
  deleteCommentConfirm: '\uC774 \uB313\uAE00\uC744 \uC0AD\uC81C\uD560\uAE4C\uC694?',
  deletePostConfirm: '\uC774 \uAC8C\uC2DC\uBB3C\uC744 \uC0AD\uC81C\uD560\uAE4C\uC694?',
  edit: '\uC218\uC815',
  guest: '\uC775\uBA85',
  goToLogin: '\uB85C\uADF8\uC778\uC73C\uB85C',
  id: '\uC544\uC774\uB514',
  invalidAuth: '\uC544\uC774\uB514\uC640 \uBE44\uBC00\uBC88\uD638\uB97C \uC785\uB825\uD574\uC8FC\uC138\uC694.',
  justNow: '\uBC29\uAE08 \uC804',
  list: '\uBAA9\uB85D',
  login: '\uB85C\uADF8\uC778',
  loginRequired: '\uB85C\uADF8\uC778\uC774 \uD544\uC694\uD569\uB2C8\uB2E4.',
  logout: '\uB85C\uADF8\uC544\uC6C3',
  password: '\uBE44\uBC00\uBC88\uD638',
  passwordMismatch: '\uBE44\uBC00\uBC88\uD638\uAC00 \uC11C\uB85C \uB2E4\uB985\uB2C8\uB2E4.',
  postImage: '\uAC8C\uC2DC\uBB3C \uC774\uBBF8\uC9C0',
  posts: '\uC790\uC720 \uAC8C\uC2DC\uD310',
  save: '\uC800\uC7A5',
  saveFailed: '\uC800\uC7A5 \uC2E4\uD328: ',
  serverJsonError: '\uC11C\uBC84\uAC00 \uC62C\uBC14\uB978 JSON\uC744 \uBC18\uD658\uD558\uC9C0 \uC54A\uC558\uC2B5\uB2C8\uB2E4: ',
  httpError: 'HTTP \uC624\uB958',
  signup: '\uD68C\uC6D0\uAC00\uC785',
  title: '\uC81C\uBAA9',
  update: '\uC218\uC815 \uC644\uB8CC',
  updateFailed: '\uC218\uC815 \uC2E4\uD328: ',
  views: '\uC870\uD68C\uC218',
  write: '\uAE00\uC4F0\uAE30',
  writeContent: '\uB0B4\uC6A9',
  writeContentRequired: '\uC81C\uBAA9\uACFC \uB0B4\uC6A9\uC744 \uC785\uB825\uD574\uC8FC\uC138\uC694.',
  writeCommentRequired: '\uB313\uAE00\uC744 \uC785\uB825\uD574\uC8FC\uC138\uC694.',
};

const requestJson = (url, options = {}) =>
  fetch(url, options).then(async res => {
    const responseText = await res.text();
    let data;

    try {
      data = responseText ? JSON.parse(responseText) : {};
    } catch (error) {
      throw new Error(`${text.serverJsonError}${responseText.slice(0, 120)}`);
    }

    if (!res.ok) {
      throw new Error(data.message || `${text.httpError} ${res.status}`);
    }

    return data;
  });

function App() {
  const [mode, setMode] = useState('list');
  const [posts, setPosts] = useState([]);
  const [selectedPost, setSelectedPost] = useState(null);
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [commentContent, setCommentContent] = useState('');
  const [comments, setComments] = useState([]);
  const [file, setFile] = useState(null);
  const [authMode, setAuthMode] = useState('login');
  const [authId, setAuthId] = useState('');
  const [authPassword, setAuthPassword] = useState('');
  const [authPasswordConfirm, setAuthPasswordConfirm] = useState('');
  const [currentUser, setCurrentUser] = useState(() => {
    localStorage.removeItem('currentUser');
    const savedUser = sessionStorage.getItem('currentUser');
    return savedUser ? JSON.parse(savedUser) : null;
  });

  const authToken = () => (currentUser && currentUser.token ? currentUser.token : '');

  const authHeaders = () => (
    authToken() ? { Authorization: `Bearer ${authToken()}` } : {}
  );

  const isMine = ownerId => (
    currentUser && ownerId !== null && ownerId !== undefined && Number(ownerId) === Number(currentUser.id)
  );

  const requireLogin = () => {
    if (currentUser && currentUser.token) return true;
    localStorage.removeItem('currentUser');
    sessionStorage.removeItem('currentUser');
    setCurrentUser(null);
    alert(text.loginRequired);
    setAuthMode('login');
    setMode('auth');
    return false;
  };

  const loadPosts = () => {
    requestJson(`${API_BASE}/posts.php`)
      .then(data => setPosts(Array.isArray(data) ? data : []))
      .catch(err => console.error('게시물 로드 실패:', err));
  };

  useEffect(() => {
    loadPosts();
  }, []);

  const resetAuthForm = () => {
    setAuthId('');
    setAuthPassword('');
    setAuthPasswordConfirm('');
  };

  const submitAuth = () => {
    const username = authId.trim();
    const password = authPassword.trim();

    if (!username || !password) {
      alert(text.invalidAuth);
      return;
    }

    if (authMode === 'signup' && password !== authPasswordConfirm.trim()) {
      alert(text.passwordMismatch);
      return;
    }

    requestJson(`${API_BASE}/${authMode === 'signup' ? 'signup.php' : 'login.php'}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password }),
    })
      .then(data => {
        if (!data.success) {
          alert(data.message || text.authFailed);
          return;
        }

        sessionStorage.setItem('currentUser', JSON.stringify(data.user));
        setCurrentUser(data.user);
        resetAuthForm();
        setMode('list');
      })
      .catch(err => alert(text.authFailed + err.message));
  };

  const logout = () => {
    localStorage.removeItem('currentUser');
    sessionStorage.removeItem('currentUser');
    setCurrentUser(null);
    setMode('list');
  };

  const saveComment = () => {
    if (!requireLogin()) return;
    if (!commentContent.trim()) {
      alert(text.writeCommentRequired);
      return;
    }

    requestJson(`${API_BASE}/comments.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...authHeaders() },
      body: JSON.stringify({
        post_id: selectedPost.id,
        content: commentContent,
        token: authToken(),
      }),
    })
      .then(data => {
        alert(data.message);
        const newComment = {
          id: data.id,
          content: commentContent,
          created_at: text.justNow,
          user_id: data.user_id,
          username: data.username,
        };
        setComments([newComment, ...comments]);
        setCommentContent('');
      })
      .catch(err => alert(text.saveFailed + err.message));
  };

  const save = () => {
    if (!requireLogin()) return;
    if (!title.trim() || !content.trim()) {
      alert(text.writeContentRequired);
      return;
    }

    const formData = new FormData();
    formData.append('title', title);
    formData.append('content', content);
    formData.append('token', authToken());

    if (file && file.length > 0) {
      for (let i = 0; i < file.length; i += 1) {
        formData.append('image[]', file[i]);
      }
    }

    requestJson(`${API_BASE}/write.php`, {
      method: 'POST',
      headers: authHeaders(),
      body: formData,
    })
      .then(data => {
        alert(data.message);
        loadPosts();
        setMode('list');
        setTitle('');
        setContent('');
        setFile(null);
      })
      .catch(err => alert(text.saveFailed + err.message));
  };

  const getComments = id => {
    requestJson(`${API_BASE}/get_comments.php?post_id=${id}`)
      .then(data => setComments(Array.isArray(data) ? data : []))
      .catch(err => console.error('댓글 로드 실패:', err));
  };

  const openPost = post => {
    setSelectedPost(post);
    setMode('detail');
    getComments(post.id);
    requestJson(`${API_BASE}/views.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: post.id }),
    }).catch(err => console.error('조회수 업데이트 실패:', err));

    const updatedPosts = posts.map(item =>
      item.id === post.id ? { ...item, views: Number(item.views) + 1 } : item
    );
    setPosts(updatedPosts);
    setSelectedPost(updatedPosts.find(item => item.id === post.id));
  };

  const deletePost = () => {
    if (!requireLogin()) return;
    if (!window.confirm(text.deletePostConfirm)) return;

    requestJson(`${API_BASE}/delete.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...authHeaders() },
      body: JSON.stringify({ id: selectedPost.id, token: authToken() }),
    })
      .then(data => {
        alert(data.message);
        if (data.success !== false) {
          setPosts(posts.filter(post => post.id !== selectedPost.id));
          setComments([]);
          setSelectedPost(null);
          setMode('list');
        }
      })
      .catch(err => console.error('게시물 삭제 실패:', err));
  };

  const deleteComment = id => {
    if (!requireLogin()) return;
    if (!window.confirm(text.deleteCommentConfirm)) return;

    requestJson(`${API_BASE}/delete_comment.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...authHeaders() },
      body: JSON.stringify({ id, token: authToken() }),
    })
      .then(data => {
        alert(data.message);
        setComments(comments.filter(comment => comment.id !== id));
      })
      .catch(err => console.error('댓글 삭제 실패:', err));
  };

  const update = () => {
    if (!requireLogin()) return;
    if (!title.trim() || !content.trim()) {
      alert(text.writeContentRequired);
      return;
    }

    requestJson(`${API_BASE}/update.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...authHeaders() },
      body: JSON.stringify({
        title,
        content,
        id: selectedPost.id,
        token: authToken(),
      }),
    })
      .then(data => {
        alert(data.message);
        const updatedPosts = posts.map(post =>
          post.id === selectedPost.id ? { ...post, title, content } : post
        );
        setPosts(updatedPosts);
        setSelectedPost({ ...selectedPost, title, content });
        setMode('detail');
        setTitle('');
        setContent('');
      })
      .catch(err => alert(text.updateFailed + err.message));
  };

  return (
    <div className="App">
      <header className="app-header">
        <h1 onClick={() => setMode('list')}>{text.posts}</h1>
        <div className="auth-area">
          {currentUser ? (
            <>
              <span>{currentUser.username}</span>
              <button onClick={logout}>{text.logout}</button>
            </>
          ) : (
            <button
              onClick={() => {
                setAuthMode('login');
                setMode('auth');
              }}
            >
              {text.login}
            </button>
          )}
        </div>
      </header>
      <hr />

      {mode === 'auth' && (
        <div className="form-container auth-form">
          <h2>{authMode === 'signup' ? text.signup : text.login}</h2>
          <input
            className="form-input"
            type="text"
            placeholder={text.id}
            value={authId}
            onChange={e => setAuthId(e.target.value)}
          />
          <input
            className="form-input"
            type="password"
            placeholder={text.password}
            value={authPassword}
            onChange={e => setAuthPassword(e.target.value)}
            onKeyDown={e => {
              if (e.key === 'Enter' && authMode === 'login') submitAuth();
            }}
          />
          {authMode === 'signup' && (
            <input
              className="form-input"
              type="password"
              placeholder={text.confirmPassword}
              value={authPasswordConfirm}
              onChange={e => setAuthPasswordConfirm(e.target.value)}
              onKeyDown={e => {
                if (e.key === 'Enter') submitAuth();
              }}
            />
          )}
          <button onClick={submitAuth}>{authMode === 'signup' ? text.signup : text.login}</button>
          <button
            onClick={() => {
              setAuthMode(authMode === 'signup' ? 'login' : 'signup');
              resetAuthForm();
            }}
          >
            {authMode === 'signup' ? text.goToLogin : text.createAccount}
          </button>
          <button onClick={() => setMode('list')}>{text.cancel}</button>
        </div>
      )}

      {mode === 'list' && (
        <div className="list-container">
          <button onClick={() => (requireLogin() ? setMode('write') : null)}>{text.write}</button>
          {posts.map(post => (
            <div className="post-card" key={post.id} onClick={() => openPost(post)}>
              <div className="post-item">
                <span className="post-title">{post.title}</span>
                <span className="post-views">{text.views} {post.views}</span>
              </div>
            </div>
          ))}
        </div>
      )}

      {mode === 'detail' && selectedPost && (
        <div>
          <div className="detail-container">
            <h2>
              {selectedPost.title}
              <span className="detail-views">{text.views} {selectedPost.views}</span>
            </h2>
            <hr />
            <p>{selectedPost.content}</p>
            {selectedPost.image_path &&
              selectedPost.image_path.split(',').map((img, index) => (
                <img
                  key={`${img}-${index}`}
                  src={`${API_BASE}/uploads/${img}`}
                  alt={`${text.postImage} ${index + 1}`}
                  className="detail-image"
                />
              ))}
            <br />
            <small>
              {text.author} {selectedPost.username || text.guest} / {text.createdAt} {selectedPost.created_at}
            </small>
            <br />
            <br />
            <button onClick={() => setMode('list')}>{text.list}</button>
            {isMine(selectedPost.user_id) && (
              <>
                <button
                  onClick={() => {
                    if (!requireLogin()) return;
                    setMode('edit');
                    setTitle(selectedPost.title);
                    setContent(selectedPost.content);
                  }}
                >
                  {text.edit}
                </button>
                <button onClick={deletePost}>{text.delete}</button>
              </>
            )}
            <div className="detail-write-comment">
              <textarea
                placeholder={text.comment}
                value={commentContent}
                onChange={e => setCommentContent(e.target.value)}
              />
              <button onClick={saveComment}>{text.add}</button>
            </div>
          </div>
          <h3>{text.comments}</h3>
          <div>
            {comments.map(item => (
              <div className="comments-list" key={item.id}>
                <strong>{item.username || text.guest}</strong>: {item.content}
                <br />
                <small>{item.created_at}</small>
                {isMine(item.user_id) && (
                  <button onClick={() => deleteComment(item.id)}>{text.delete}</button>
                )}
              </div>
            ))}
          </div>
        </div>
      )}

      {mode === 'write' && (
        <div className="form-container">
          <h2>{text.write}</h2>
          <input
            className="form-input"
            type="text"
            placeholder={text.title}
            value={title}
            onChange={e => setTitle(e.target.value)}
          />
          <textarea
            className="form-textarea"
            placeholder={text.writeContent}
            value={content}
            onChange={e => setContent(e.target.value)}
          />
          <input
            multiple
            className="form-fileuploads"
            type="file"
            accept="image/*"
            onChange={e => setFile(e.target.files)}
          />
          <button onClick={save}>{text.save}</button>
          <button onClick={() => setMode('list')}>{text.cancel}</button>
        </div>
      )}

      {mode === 'edit' && (
        <div className="form-container">
          <h2>{text.edit}</h2>
          <input className="form-input" value={title} onChange={e => setTitle(e.target.value)} />
          <textarea
            className="form-textarea"
            value={content}
            onChange={e => setContent(e.target.value)}
          />
          <button onClick={update}>{text.update}</button>
          <button onClick={() => setMode('detail')}>{text.cancel}</button>
        </div>
      )}
    </div>
  );
}

export default App;
