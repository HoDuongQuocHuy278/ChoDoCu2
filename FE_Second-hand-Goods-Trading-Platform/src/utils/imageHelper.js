import { APP_URL } from '../config';

export const fixImageUrl = (url) => {
    if (!url) return '';
    if (url.startsWith('http') || url.startsWith('data:')) {
        return url;
    }
    // Remove leading slash if present to avoid double slashes
    const cleanPath = url.startsWith('/') ? url.substring(1) : url;
    return `${APP_URL}/${cleanPath}`;
};
