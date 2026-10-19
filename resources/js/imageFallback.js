const PLACEHOLDER = 'data:image/svg+xml,' + encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 3"><rect width="4" height="3" fill="#e5e7eb"/></svg>'
)

/**
 * Swap a broken image for `fallback` once, then for a plain placeholder, then stop.
 * Setting src to a fallback that is also missing re-fires the error event,
 * so without this guard the browser requests the same 404 forever.
 */
export const onImageError = (event, fallback) => {
    const img = event.target

    if (img.dataset.fallback === 'done') {
        return
    }

    if (!img.dataset.fallback && fallback && !img.src.endsWith(fallback)) {
        img.dataset.fallback = 'tried'
        img.src = fallback
        return
    }

    img.dataset.fallback = 'done'
    img.src = PLACEHOLDER
}
