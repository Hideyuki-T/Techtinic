// jest.config.js
module.exports = {
    transform: {
        '^.+\\.js$': 'babel-jest'
    },
    transformIgnorePatterns: [
        '/node_modules/(?!(node-fetch)/)'
    ],
    moduleNameMapper: {
        '^/js/(.*)$': '<rootDir>/public/js/$1'
    },
    setupFiles: ['<rootDir>/jest.setup.js'],
};


