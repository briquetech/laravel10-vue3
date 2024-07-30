import {DateTime} from "luxon";

export default {
	data() {
		return {};
	},
	mounted() {},
	methods: {
		async loadAllDepartments(active) {
			var URL = "/api/department/get";
			let allRecords = [];
			var postArr = {};
			if (active) postArr["active"] = 1;
			await axios
				.post(URL, postArr)
				.then(function (response) {
					allRecords = response.data.data;
				})
				.catch(function (error) {
					console.log(error);
				});
			return allRecords;
		},
		async loadAllRole(active) {
			var URL = "/api/role/get";
			let allRecords = [];
			var postArr = {};
			if (active) postArr["active"] = 1;
			await axios
				.post(URL, postArr)
				.then(function (response) {
					allRecords = response.data.data;
				})
				.catch(function (error) {
					console.log(error);
				});
			return allRecords;
		},
		async loadAllUser(active) {
			var URL = "/api/user/get";
			let allUsers = [];
			var postArr = {};
			if (active) postArr["active"] = 1;
			await axios
				.post(URL, postArr)
				.then(function (response) {
					allUsers = response.data.data;
				})
				.catch(function (error) {
					console.log(error);
				});
			return allUsers;
		}
	}
};
