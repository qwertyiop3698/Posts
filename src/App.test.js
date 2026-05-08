import { render, screen } from '@testing-library/react';
import App from './App';

test('자유 게시판 제목을 보여준다', () => {
  render(<App />);
  const titleElement = screen.getByText(/자유 게시판/i);
  expect(titleElement).toBeInTheDocument();
});
