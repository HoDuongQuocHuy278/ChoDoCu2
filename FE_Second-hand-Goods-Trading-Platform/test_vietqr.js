// Test script for VietQR generation logic
// This script simulates the logic used in Checkout/index.vue

const banks = [
    { bin: '970415', shortName: 'VietinBank', name: 'Ngân hàng TMCP Công thương Việt Nam' },
    { bin: '970436', shortName: 'Vietcombank', name: 'Ngân hàng TMCP Ngoại thương Việt Nam' },
    { bin: '970418', shortName: 'BIDV', name: 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam' },
    { bin: '970422', shortName: 'MBBank', name: 'Ngân hàng TMCP Quân đội' },
    // ... (subset for testing)
];

const generateVietQR = (bankName, accountNumber, accountName, amount, content) => {
    if (!bankName || !accountNumber) return null

    // Tìm Bin code từ tên ngân hàng (shortName)
    const bank = banks.find(b => b.shortName === bankName || b.name === bankName)
    const bin = bank ? bank.bin : '970422' // Default MBBank if not found

    const cleanAccount = accountNumber.replace(/[^a-zA-Z0-9]/g, '')
    const cleanName = encodeURIComponent(accountName || '')
    const cleanContent = encodeURIComponent(content || '')

    return `https://img.vietqr.io/image/${bin}-${cleanAccount}-compact2.jpg?amount=${amount}&addInfo=${cleanContent}&accountName=${cleanName}`
}

// Test Cases
const tests = [
    {
        name: 'Standard Case (VietinBank)',
        input: { bank: 'VietinBank', acc: '113366668888', name: 'Quy Vac Xin Covid', amount: 790000, content: 'dong qop quy vac xin' },
        expectedBin: '970415'
    },
    {
        name: 'MBBank Case',
        input: { bank: 'MBBank', acc: '0901234567', name: 'NGUYEN VAN A', amount: 50000, content: 'Thanh toan don hang' },
        expectedBin: '970422'
    },
    {
        name: 'Unknown Bank (Default to MBBank)',
        input: { bank: 'UnknownBank', acc: '123456', name: 'Test', amount: 10000, content: 'Test' },
        expectedBin: '970422'
    },
    {
        name: 'Special Characters in Content',
        input: { bank: 'Vietcombank', acc: '0011001234567', name: 'SHOP ABC', amount: 100000, content: 'Thanh toán #123' },
        expectedBin: '970436'
    }
];

console.log('Running VietQR Logic Tests...\n');

let passed = 0;
tests.forEach(test => {
    const result = generateVietQR(test.input.bank, test.input.acc, test.input.name, test.input.amount, test.input.content);
    console.log(`Test: ${test.name}`);
    console.log(`Result: ${result}`);

    if (result && result.includes(test.expectedBin) && result.includes(test.input.acc)) {
        console.log('✅ PASSED\n');
        passed++;
    } else {
        console.log('❌ FAILED\n');
    }
});

console.log(`Total: ${tests.length}, Passed: ${passed}, Failed: ${tests.length - passed}`);
