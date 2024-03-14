import { DateTime } from "luxon";

export default {
    data() {
        return {};
    },
    mounted() {},
    methods: {
        validateEmail(emailString) {
            // Pending email regex
            return emailString && emailString.length > 0;
        },
        validatePhone(phoneString) {
            return (
                phoneString &&
                phoneString.length > 0 &&
                /^\d{5,}$/gi.test(phoneString)
            );
        },
        formatMySQLDate(targetDate, dateFormat) {
            return DateTime.fromSQL(targetDate).toFormat(dateFormat);
        },
        formatISODate(targetDate, dateFormat) {
            return DateTime.fromISO(targetDate).toFormat(dateFormat);
        },
        today() {
            return new DateTime(new Date()).toFormat("yyyy-MM-dd");
        },
        tomorrow() {
            return new DateTime(new Date()).toFormat("yyyy-MM-dd");
        },
        minutesAgo(isoDate) {
            const targetDate = new Date(isoDate);
            const currentDate = new Date();
            const timeDifference = currentDate - targetDate;
            return Math.floor(timeDifference / 1000 / 60);
        },
    },
};
