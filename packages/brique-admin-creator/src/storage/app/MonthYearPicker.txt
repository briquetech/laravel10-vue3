<template>
	<div class="input-group">
		<select v-model="selectedMonth" @change="handleChange" :class="class">
			<option v-for="month in months" :key="month">{{ month }}</option>
		</select>
		<select v-model="selectedYear" @change="handleChange" :class="class">
			<option v-for="year in years" :key="year">{{ year }}</option>
		</select>
	</div>
</template>

<script>
	export default {
		props: {
			min: {
				type: Date,
				default: new Date(1970, 0, 1) // January 1st, 1970
			},
			max: {
				type: Date,
				default: new Date(2070, 11, 31) // January 1st, 1970
			},
			order: {
				type: String,
				default: "desc"
			},
			returnmode: {
				type: String,
				default: "Date"
			},
			class: {
				type: String,
				default: ""
			},
			modelValue: {
				type: String,
				default: '',
			},
		},
		data() {
			return {
				months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
				selectedMonth: null,
				selectedYear: null
			};
		},
		computed: {
			years() {
				const minYear = this.min.getFullYear();
				const maxYear = this.max.getFullYear();
				if (this.order == "asc") return Array.from({length: maxYear - minYear + 1}, (_, i) => i + minYear);
				else return Array.from({length: maxYear - minYear + 1}, (_, i) => maxYear - i);
			}
		},
		methods: {
			handleChange() {
				if (this.selectedMonth && this.selectedYear) {
					let selectedDate = new Date(`${this.selectedYear}-${this.selectedMonth}-01`);
					// Validate the selected date against min, max, and maxDate
					if (selectedDate < this.min) {
						this.selectedYear = this.min.getFullYear();
						this.selectedMonth = this.months[this.min.getMonth()];
					} else if (selectedDate > this.maxDate) {
						this.selectedYear = this.maxDate.getFullYear();
						this.selectedMonth = this.months[this.maxDate.getMonth()];
					} else if (selectedDate > this.max) {
						this.selectedYear = this.max.getFullYear();
						this.selectedMonth = this.months[this.max.getMonth()];
					}
					selectedDate = new Date(`${this.selectedYear}-${this.selectedMonth}-01`);
					if (this.returnmode.toLowerCase() == "date") this.$emit("update:modelValue", selectedDate);
					else {
						this.$emit("update:modelValue", selectedDate.getFullYear() + "-" + (selectedDate.getMonth() <= 8 ? "0" : "") + (selectedDate.getMonth() + 1) + "-01");
					}
				}
			},
			isMonthDisabled(month) {
				const selectedDate = new Date(`${this.selectedYear}-${month}-01`);
				return selectedDate < this.min || selectedDate > this.maxDate || selectedDate > this.max;
			},
			isYearDisabled(year) {
				const selectedDate = new Date(`${year}-${this.selectedMonth}-01`);
				return selectedDate < this.min || selectedDate > this.maxDate || selectedDate > this.max;
			}
		},
		watch: {
			modelValue: {
				immediate: true,
				handler(newValue, oldValue) {
					console.log(newValue);
					if (newValue !== null && newValue !== undefined) {
						let result = /^\d{4}-\d{2}-\d{2}$/gm.test(newValue);
						console.log("not null" + newValue + " :: " + result);
						let newDate = null;
						if (result) {
							newDate = new Date(Date.parse(newValue));
							console.log("newDate ", newDate);
						}
						if (newDate instanceof Date) {
							console.log("is Date");
							this.selectedYear = newDate.getFullYear();
							this.selectedMonth = this.months[newDate.getMonth()]; //this.months[parseInt(month) - 1];
							let selectedDate = new Date(`${this.selectedYear}-${this.selectedMonth}-01`);
							console.log("MYP selectedDate " + selectedDate);
							this.$emit("update:modelValue", selectedDate.getFullYear() + "-" + (selectedDate.getMonth() <= 8 ? "0" : "") + (selectedDate.getMonth() + 1) + "-01");
						}
					}
				}
			}
		}
	};
</script>