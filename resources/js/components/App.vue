<template>
    <div id="content">
        <form @submit.prevent="submitUrl" class="form-inline">
            <h1 class="heading">URL Shortener</h1>
            <div class="input-group">
                <input
                    id="search"
                    class="form-control search-form"
                    type="text"
                    v-model="url"
                    placeholder="Enter URL"
                />
                <span class="input-group-btn" style="width: 100px">
                    <button
                        id="short-this"
                        class="pull-right btn btn-default search-btn"
                        type="submit"
                    >
                        Shorten URL
                    </button>
                </span>
            </div>
            <div v-if="shortUrl">
                <p class="text-center">
                    Shortened URL:
                    <a :href="shortUrl" class="url" target="_blank">{{
                        shortUrl
                    }}</a>
                </p>
            </div>
            <div v-if="errorMessage">
                <p class="text-center" style="color: #ffaeae">
                    {{ errorMessage }}
                </p>
            </div>
        </form>
    </div>
</template>

<script>
import axios from "axios";
export default {
    data() {
        return {
            url: "",
            shortUrl: null,
            errorMessage: null,
        };
    },
    methods: {
        async submitUrl() {
            try {
                const response = await axios.post("/urls", { url: this.url });
                this.shortUrl = response.data.short_url;
                this.errorMessage = null;
                this.url = "";
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errorMessage = "Please enter a valid URL.";
                } else if (error.response && error.response.status === 400) {
                    this.errorMessage = "URL is malicious or not safe";
                } else {
                    this.errorMessage =
                        "An error occurred. Please try again later.";
                }
                this.shortUrl = null;
            }
        },
    },
};
</script>
