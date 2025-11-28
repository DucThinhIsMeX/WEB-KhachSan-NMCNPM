class Validation {
    static required(value, fieldName) {
        if (!value || value.trim() === '') {
            return `${fieldName} không được để trống`;
        }
        return null;
    }

    static isNumber(value, fieldName) {
        if (isNaN(value)) {
            return `${fieldName} phải là số`;
        }
        return null;
    }

    static min(value, minValue, fieldName) {
        if (parseFloat(value) < minValue) {
            return `${fieldName} phải lớn hơn hoặc bằng ${minValue}`;
        }
        return null;
    }

    static max(value, maxValue, fieldName) {
        if (parseFloat(value) > maxValue) {
            return `${fieldName} phải nhỏ hơn hoặc bằng ${maxValue}`;
        }
        return null;
    }

    static email(value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            return 'Email không hợp lệ';
        }
        return null;
    }

    static phone(value) {
        const phoneRegex = /^[0-9]{10,11}$/;
        if (!phoneRegex.test(value)) {
            return 'Số điện thoại không hợp lệ';
        }
        return null;
    }

    static cmnd(value) {
        const cmndRegex = /^[0-9]{9,12}$/;
        if (!cmndRegex.test(value)) {
            return 'CMND/CCCD không hợp lệ';
        }
        return null;
    }

    static validateForm(formData, rules) {
        const errors = {};
        
        for (const field in rules) {
            const value = formData[field];
            const fieldRules = rules[field];

            for (const rule of fieldRules) {
                const error = rule(value);
                if (error) {
                    errors[field] = error;
                    break;
                }
            }
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors: errors
        };
    }
}
