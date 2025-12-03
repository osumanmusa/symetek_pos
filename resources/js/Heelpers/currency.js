// Currency formatting helper
export const useCurrency = () => {
  const format = (amount, options = {}) => {
    const config = window.App?.currency || {
      symbol: 'GH₵',
      code: 'GHS',
      name: 'Ghana Cedi',
      position: 'before',
      decimals: 2,
      thousandsSeparator: ',',
      decimalSeparator: '.'
    }
    
    const {
      symbol = config.symbol,
      position = config.position,
      decimals = config.decimals,
      thousandsSeparator = config.thousandsSeparator,
      decimalSeparator = config.decimalSeparator,
      showSymbol = true
    } = options
    
    let amountNumber = parseFloat(amount) || 0
    
    // Format the number
    const fixedAmount = amountNumber.toFixed(decimals)
    const parts = fixedAmount.split('.')
    
    // Add thousands separator
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator)
    
    // Join with decimal separator
    const formattedNumber = parts.join(decimalSeparator)
    
    // Add currency symbol
    if (!showSymbol) {
      return formattedNumber
    }
    
    if (position === 'after') {
      return `${formattedNumber} ${symbol}`
    } else {
      return `${symbol}${formattedNumber}`
    }
  }
  
  const formatNumber = (amount, decimals = 2) => {
    return parseFloat(amount || 0).toFixed(decimals)
  }
  
  return { format, formatNumber }
}

// Make it available globally
export default useCurrency