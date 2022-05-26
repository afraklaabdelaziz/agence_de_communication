/*
 * For a detailed explanation regarding each configuration property and type check, visit:
 * https://jestjs.io/docs/configuration
 */

export default {
  verbose: true,
  clearMocks: true,
  moduleDirectories: [
    'node_modules',
    '<rootDir>/assets/js',
  ],
  roots: [
    '<rootDir>/assets/js',
    '<rootDir>/tests/jest',
  ],
  setupFiles: ['<rootDir>/tests/jest/setup.cjs'],
  testEnvironment: 'jsdom',
  transform: {},
  preset: 'ts-jest'
};
