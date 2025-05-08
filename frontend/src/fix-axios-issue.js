// Dummy axios module to satisfy Vite's dependency resolution
// This is a workaround for the "Error: ENOENT: no such file or directory, open '.../node_modules/axios/index.js'" error

export default {
  get: () => Promise.resolve({ data: {} }),
  post: () => Promise.resolve({ data: {} }),
  put: () => Promise.resolve({ data: {} }),
  delete: () => Promise.resolve({ data: {} }),
  create: () => ({
    get: () => Promise.resolve({ data: {} }),
    post: () => Promise.resolve({ data: {} }),
    put: () => Promise.resolve({ data: {} }),
    delete: () => Promise.resolve({ data: {} })
  })
}; 