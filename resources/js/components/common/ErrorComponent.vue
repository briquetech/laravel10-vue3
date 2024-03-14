<template>
  <div class="alert alert-danger" v-if="errors && errors.length > 0">
      <ul v-for="(error, index) of errors" :key="index">
        <li>{{  error }}</li>
      </ul>
  </div>
</template>

<script>
export default {
  name: "error",
  created() {},
  props: {
    errors:{ type:Array, default:[] }
  },
  mounted(){
    console.log(this.errors);
  }
};
</script>

<style lang="scss" scoped></style>