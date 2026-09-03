const getPreferredLocale = () => {
  if (typeof navigator !== 'undefined') {
    return navigator.language || navigator.languages?.[0] || 'en'
  }

  return 'en'
}

const parseDateValue = (value) => {
  if (value instanceof Date) {
    return value
  }

  if (value === null || value === undefined || value === '') {
    return null
  }

  return new Date(value)
}

export function formatDate(value, options = {}) {
  const date = parseDateValue(value)

  if (!date || Number.isNaN(date.getTime())) {
    return ''
  }

  return new Intl.DateTimeFormat(getPreferredLocale(), {
    dateStyle: 'medium',
    ...options,
  }).format(date)
}

export function formatTime(value, options = {}) {
  const date = parseDateValue(value)

  if (!date || Number.isNaN(date.getTime())) {
    return ''
  }

  return new Intl.DateTimeFormat(getPreferredLocale(), {
    timeStyle: 'short',
    ...options,
  }).format(date)
}

export function formatDateTime(value, options = {}) {
  const date = parseDateValue(value)

  if (!date || Number.isNaN(date.getTime())) {
    return ''
  }

  return new Intl.DateTimeFormat(getPreferredLocale(), {
    dateStyle: 'medium',
    timeStyle: 'short',
    ...options,
  }).format(date)
}
