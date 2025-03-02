// jest.setup.js
global.indexedDB = require('fake-indexeddb');
global.IDBKeyRange = require('fake-indexeddb/lib/FDBKeyRange');
// 必要に応じて他の IndexedDB のグローバル変数も設定できます
