import { vi } from 'vitest';

vi.stubGlobal('IntersectionObserver', class {
  observe() {}
  disconnect() {}
  unobserve() {}
});
