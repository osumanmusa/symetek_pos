// Format price in Ghanaian Cedis
export function formatCurrency(amount) {
    const num = parseFloat(amount) || 0;
    return `₵${num.toFixed(2)}`;
}

// Format price with currency code
export function formatCurrencyWithCode(amount) {
    const num = parseFloat(amount) || 0;
    return `GHS ${num.toFixed(2)}`;
}

// Format price for input/display
export function formatPrice(amount) {
    const num = parseFloat(amount) || 0;
    return num.toFixed(2);
}

// Calculate profit margin
export function calculateMargin(cost, selling) {
    const costNum = parseFloat(cost) || 0;
    const sellingNum = parseFloat(selling) || 0;
    
    if (costNum === 0) return 100;
    return ((sellingNum - costNum) / costNum) * 100;
}