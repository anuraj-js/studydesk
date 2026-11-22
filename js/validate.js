const Validation = {
    isEmail: (email) => {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    isStrongPassword: (password) => {
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,64}$/.test(password);
    },

    isPastDate: (dateString) => {
        const selectedDate = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selectedDate < today;
    },

    // Added new validator for combined Date + Time
    isPastDateTime: (dateString, timeString) => {
        if (!dateString || !timeString) return false;
        // Parse as local time
        const selectedDateTime = new Date(dateString + 'T' + timeString);
        const now = new Date();
        // Compare absolute timestamps
        return selectedDateTime < now;
    }
};