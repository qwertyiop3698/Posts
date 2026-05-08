# 자유 게시판

React 프론트엔드와 PHP 백엔드로 만든 간단한 게시판입니다. 닷홈 배포를 기준으로 동작하며, API 주소는 `https://toma2025.dothome.co.kr/posts/backend`를 사용합니다.

## 주요 기능

- 회원가입과 로그인
- 게시물 작성, 조회, 수정, 삭제
- 댓글 작성과 삭제
- 게시물 삭제 시 댓글 함께 삭제
- 작성자 본인만 게시물 수정/삭제 가능
- 작성자 본인만 댓글 삭제 가능
- 로그인은 현재 브라우저 세션 동안만 유지

## 폴더 구조

```text
src/
  App.js
  App.scss
backend/
  auth.php
  db.php
  login.php
  signup.php
  posts.php
  write.php
  update.php
  delete.php
  comments.php
  get_comments.php
  delete_comment.php
  views.php
  uploads/
```

## 배포 방법

프론트엔드는 빌드 후 `build` 폴더 안의 내용물을 닷홈 `/posts/`에 올립니다.

```bash
npm run build
```

업로드 결과는 아래처럼 배치합니다.

```text
/posts/
  index.html
  static/
  asset-manifest.json
  manifest.json
  backend/
```

백엔드는 `backend` 폴더 전체를 닷홈 `/posts/backend/`에 업로드합니다.

## 닷홈 확인 주소

```text
https://toma2025.dothome.co.kr/posts/
```

## 테스트 방법

테스트용 계정은 회원가입 화면에서 아래 정보로 1개 생성한 뒤 사용합니다.

```text
아이디: testuser
비밀번호: test1234
```

간단 테스트 순서:

1. `https://toma2025.dothome.co.kr/posts/` 접속
2. `회원가입`에서 테스트용 계정 생성
3. 로그인 후 `글쓰기`로 제목, 내용, 이미지 파일을 넣고 저장
4. 목록에서 새 게시물이 보이는지 확인
5. 게시물을 클릭해서 상세 화면 진입 후 조회수가 증가하는지 확인
6. 댓글을 등록하고 댓글 목록에 표시되는지 확인
7. 본인이 작성한 게시물에서 `수정`으로 제목 또는 내용 변경
8. 본인이 작성한 댓글을 삭제
9. 본인이 작성한 게시물을 삭제하고, 연결된 댓글도 함께 사라지는지 확인

## 데이터베이스

`users` 테이블과 작성자 컬럼은 백엔드에서 필요 시 자동 생성합니다.

자동으로 추가되는 컬럼:

```text
users.api_token
posts.user_id
comments.user_id
```

기존 게시물과 댓글은 작성자 정보가 없을 수 있으므로, 새로 로그인 후 작성한 데이터부터 작성자별 수정/삭제 권한이 정확히 적용됩니다.

## 업로드 주의사항

- `backend/uploads/` 폴더는 이미지 업로드를 위해 서버 쓰기 권한이 필요합니다.
- `backend/db.php`에는 DB 접속 정보가 있으므로 저장소에 올리지 않습니다. 필요한 형식은 `backend/db.example.php`를 참고합니다.
- 백엔드 파일을 수정한 뒤에는 닷홈 `/posts/backend/`에 다시 업로드해야 합니다.
- 프론트 파일을 수정한 뒤에는 `npm run build` 후 `build` 안의 내용물을 `/posts/`에 다시 업로드해야 합니다.

## 개발 명령어

```bash
npm start
npm run build
npm test
```
