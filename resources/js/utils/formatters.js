export const formatDate = (dateStr) => {
    const date = new Date(dateStr)
    const year = date.getFullYear()
    const month = date.getMonth() + 1
    const day = date.getDate()
    return `${month}/${day}/${year}`
}

export const formatPrice = (price) => {
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(price)
}

// For datetime strings from DB (e.g. "2026-03-15 09:00:00")
export const formatTime = (dateStr) => {
    const date = new Date(dateStr)
    const hours = date.getHours().toString().padStart(2, '0')
    const minutes = date.getMinutes().toString().padStart(2, '0')
    const d = new Date()
    d.setHours(hours)
    d.setMinutes(minutes)
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// For time-only strings (e.g. space open_time "09:00:00")
export const formatTimeStr = (timeStr) => {
    const [hour, minute] = timeStr.split(':')
    const date = new Date()
    date.setHours(hour)
    date.setMinutes(minute)
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
