export const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,20}$/;
export const isValidEmail = (value: string): boolean => emailRegex.test(value);

export const phoneRegex = /^\+\d{9,15}$/
export const isValidPhone = (value: string): boolean => phoneRegex.test(value);