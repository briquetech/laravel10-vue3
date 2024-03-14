import {DateTime} from "luxon";

export default {
	data() {
		return {};
	},
	mounted() {},
	methods: {
		async loadAllUser(active) {
			var URL = "/api/user/get";
			let allUsers = [];
			var postArr = {};
			if (active) postArr["active"] = 1;
			await axios
				.post(URL, postArr)
				.then(function (response) {
					allUsers = response.data;
				})
				.catch(function (error) {
					console.log(error);
				});
			return allUsers;
		}
	}
};
