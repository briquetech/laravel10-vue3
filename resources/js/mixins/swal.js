import Swal from "sweetalert2";
import * as bootstrap from 'bootstrap';
export default {
    data() {
        return {};
    },
    mounted() {},
    methods: {
        showToast(text, icon, position, timer) {
			let background = "#fff";
			let color = "#000";
			if (icon == "success") {
				background = "#758E4F";
				color = "#ffffff";
			}
			else if (icon == "error") {
				background = "#E5CDC8";
				color = "#000000";
			}
            return Swal.fire({
                icon: icon,
                text: text,
                toast: true,
                position: position,
				background: background,
				color: color, 
                showConfirmButton: false,
                timer: timer,
                // timerProgressBar: true,
            });
		},
		showErrors(toastText, messages, position, timer) {
            // Show toast
			this.showToast(toastText, "error", position, timer);
			if (messages !== null && messages !== undefined && messages instanceof Array && messages.length > 0) {
				const errorModal = new bootstrap.Modal( document.getElementById("errorModal"), {} );
				let innerContents = "<ul>";
				messages.forEach((value) => {
                    innerContents += "<li>" + value + "</li>";
                });
				innerContents += "</ul>";
				document.getElementById("errorModalBodyContents").innerHTML = innerContents;
				errorModal.show();				
			}
		},
        showLoading(text) {
            return Swal.fire({
                html:
                    '<div class="d-flex justify-content-center">' +
                    '<div class="spinner-border" role="status">' +
                    '<span class="visually-hidden">' +
                    text +
                    "</span>" +
                    "</div>" +
                    "</div>" +
                    '<h2 class="mt-3">' +
                    text +
                    "</h2>",
                showConfirmButton: false,
				allowOutsideClick: false,
				allowEscapeKey: false,
            });
        },
        // showConfirm(confirmText) {
        //     return Swal.fire({
        //         text: confirmText,
        //         showDenyButton: true,
        //         showCancelButton: true,
        //         confirmButtonText: "Ok",
        //         cancelButtonText: "Cancel",
        //     });
        // },
		showConfirm(confirmText, okButtonText, cancelButtonText) {
			if (!okButtonText || okButtonText == "") okButtonText = "Ok";
			if (!cancelButtonText || cancelButtonText == "")
                cancelButtonText = "Cancel";
            return Swal.fire({
                text: confirmText,
                showCancelButton: true,
                confirmButtonText: okButtonText,
                cancelButtonText: cancelButtonText,
                customClass: {
                    confirmButton: "btn",
                    cancelButton: "btn",
                },
				allowOutsideClick: false,
				allowEscapeKey: false,
            });
        },
        closeSwal() {
            Swal.close();
        },
    },
};
