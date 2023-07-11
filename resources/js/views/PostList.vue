<template>
    <section>
        <page-header>
            <template slot="options">
                <div class="dropdown">
                    <a
                        id="navbarDropdown"
                        class="nav-link pr-1"
                        href="#"
                        role="button"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            width="25"
                            class="icon-dots-horizontal"
                        >
                            <path
                                class="fill-light-gray"
                                fill-rule="evenodd"
                                d="M5 14a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm7 0a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm7 0a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"
                            />
                        </svg>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right">
                        <router-link :to="{ name: 'create-post' }" class="dropdown-item">
                            {{ trans.new_post }}
                        </router-link>
                    </div>
                </div>
            </template>
        </page-header>

        <main v-if="isReady" class="py-4">
            <div class="col-xl-8 offset-xl-2 col-lg-10 offset-lg-1 col-md-12">
                <div class="d-flex justify-content-between mt-2 mb-4 align-items-end">
                    <h3 class="mt-2">{{ trans.posts }}</h3>

                    <select
                        v-model="type"
                        id=""
                        name=""
                        class="ml-auto w-auto custom-select border-0"
                        @change="changeType"
                    >
                        <option value="approved">Yayında ({{ suffixedNumber(approvedCount) }})</option>
                        <option value="published">Yayına Sunulan ({{ suffixedNumber(publishedCount) }})</option>
                        <option value="my-draft">Tasklaklarım ({{ suffixedNumber(myDraftCount) }})</option>
                        <option value="relevant-draft" v-if="isEditor || isAdmin">
                            Alakalı Tasklaklar ({{ suffixedNumber(relevantDraftCount) }})
                        </option>
                        <option value="all-draft" v-if="isAdmin">
                            Tüm Taslaklar ({{ suffixedNumber(allDraftCount) }})
                        </option>
                    </select>
                </div>

                <div class="mt-5 card shadow-lg">
                    <div class="card-body p-0">
                        <div :key="`${index}-${post.uuid}`" v-for="(post, index) in posts">
                            <router-link
                                :to="{
                                    name: 'edit-post',
                                    params: { id: post.uuid },
                                }"
                                class="text-decoration-none"
                            >
                                <div
                                    v-hover="{ class: `hover-bg` }"
                                    class="d-flex p-3 align-items-center"
                                    :class="{
                                        'border-top': index !== 0,
                                        'rounded-top': index === 0,
                                        'rounded-bottom': index === posts.length - 1,
                                    }"
                                >
                                    <div class="pl-2 col-md-8 col-sm-10 col-10 py-1">
                                        <p class="text-truncate lead font-weight-bold mb-0">
                                            {{ post.title }}
                                        </p>
                                        <p v-if="post.summary" class="text-truncate text-secondary my-1">
                                            {{ post.summary }}
                                        </p>
                                        <hr />
                                        <div class="row text-secondary my-1">
                                            <div v-if="post.user" class="col-sm">
                                                <p class="mb-0">Writer</p>
                                                <router-link
                                                    :to="{
                                                        name: !isAdmin ? 'posts' : 'edit-user',
                                                        params: { id: post.user.id },
                                                    }"
                                                >
                                                    <img
                                                        :src="post.user.avatar || post.user.default_avatar"
                                                        class="mr-1 rounded-circle shadow-inner"
                                                        style="width: 20px"
                                                        :alt="post.user.name"
                                                    />
                                                    {{ post.user.name }}
                                                    <br />
                                                    <small class="text-secondary mb-0 text-xs">{{
                                                        moment(post.created_at).fromNow()
                                                    }}</small>
                                                </router-link>
                                            </div>
                                            <div v-if="post.reviewer" class="col-sm">
                                                <p class="mb-0">Reviewer</p>
                                                <router-link
                                                    :to="{
                                                        name: !isAdmin ? 'posts' : 'edit-user',
                                                        params: { id: post.reviewer.id },
                                                    }"
                                                >
                                                    <img
                                                        :src="post.reviewer.avatar || post.reviewer.default_avatar"
                                                        class="mr-1 rounded-circle shadow-inner"
                                                        style="width: 20px"
                                                        :alt="post.reviewer.name"
                                                    />
                                                    {{ post.reviewer.name }}
                                                </router-link>
                                            </div>
                                            <div v-if="post.approver" class="col-sm">
                                                <p class="mb-0">Approver</p>
                                                <router-link
                                                    :to="{
                                                        name: !isAdmin ? 'posts' : 'edit-user',
                                                        params: { id: post.approver.id },
                                                    }"
                                                >
                                                    <img
                                                        :src="post.approver.avatar || post.approver.default_avatar"
                                                        class="mr-1 rounded-circle shadow-inner"
                                                        style="width: 20px"
                                                        :alt="post.approver.name"
                                                    />
                                                    {{ post.approver.name }}
                                                    <br />
                                                    <small class="text-secondary mb-0 text-sm">{{
                                                        moment(post.approved_at).fromNow()
                                                    }}</small>
                                                </router-link>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-auto d-none d-md-inline pl-3">
                                        <div
                                            id="featuredImage"
                                            v-if="post.featured_image"
                                            class="mr-2 ml-3 shadow-inner"
                                            :style="{
                                                backgroundImage: 'url(' + post.featured_image + ')',
                                            }"
                                        />
                                        <div v-else class="mx-3 align-middle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="45"
                                                viewBox="0 0 24 24"
                                                class="icon-camera"
                                            >
                                                <path
                                                    class="fill-light-gray"
                                                    d="M6.59 6l2.7-2.7A1 1 0 0 1 10 3h4a1 1 0 0 1 .7.3L17.42 6H20a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8c0-1.1.9-2 2-2h2.59zM19 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-7 8a5 5 0 1 0 0-10 5 5 0 0 0 0 10z"
                                                />
                                                <path class="fill-light-gray" d="M12 16a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="d-inline d-md-none pl-3 ml-auto">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="25"
                                            viewBox="0 0 24 24"
                                            class="icon-cheveron-right-circle"
                                        >
                                            <circle cx="12" cy="12" r="10" style="fill: none" />
                                            <path
                                                class="fill-light-gray"
                                                d="M10.3 8.7a1 1 0 0 1 1.4-1.4l4 4a1 1 0 0 1 0 1.4l-4 4a1 1 0 0 1-1.4-1.4l3.29-3.3-3.3-3.3z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </router-link>
                        </div>

                        <infinite-loading :identifier="infiniteId" spinner="spiral" @infinite="fetchPosts">
                            <span slot="no-more" />
                            <div slot="no-results" class="text-left">
                                <div class="my-5">
                                    <p class="lead text-center text-muted mt-5">
                                        <span v-if="type === 'published'">{{ trans.you_have_no_published_posts }}</span>
                                        <span v-else>{{ trans.you_have_no_draft_posts }}</span>
                                    </p>
                                    <p class="lead text-center text-muted mt-1">
                                        {{ trans.write_on_the_go }}
                                    </p>
                                </div>
                            </div>
                        </infinite-loading>
                    </div>
                </div>
            </div>
        </main>
    </section>
</template>

<script>
import { mapGetters } from 'vuex';
import Hover from '../directives/Hover';
import InfiniteLoading from 'vue-infinite-loading';
import NProgress from 'nprogress';
import PageHeader from '../components/PageHeader';
import isEmpty from 'lodash/isEmpty';
import status from '../mixins/status';
import strings from '../mixins/strings';

export default {
    name: 'post-list',

    components: {
        InfiniteLoading,
        PageHeader,
    },

    directives: {
        Hover,
    },

    mixins: [status, strings],

    data() {
        return {
            page: 1,
            posts: [],
            publishedCount: 0,
            approvedCount: 0,
            myDraftCount: 0,
            relevantDraftCount: 0,
            allDraftCount: 0,
            type: 'approved',
            infiniteId: +new Date(),
            isReady: false,
        };
    },

    computed: {
        ...mapGetters({
            isContributor: 'settings/isContributor',
            isAdmin: 'settings/isAdmin',
            isEditor: 'settings/isEditor',
            trans: 'settings/trans',
        }),
    },

    created() {
        this.fetchPosts();
        this.isReady = true;
        NProgress.done();
    },

    methods: {
        fetchPosts($state) {
            if ($state) {
                return this.request()
                    .get('/api/posts', {
                        params: {
                            page: this.page,
                            type: this.type,
                            scope: this.isContributor ? 'user' : 'all',
                        },
                    })
                    .then(({ data }) => {
                        this.publishedCount = data.publishedCount;
                        this.approvedCount = data.approvedCount;
                        this.myDraftCount = data.myDraftCount;
                        this.relevantDraftCount = data.relevantDraftCount;
                        this.allDraftCount = data.allDraftCount;

                        if (!isEmpty(data) && !isEmpty(data.posts.data)) {
                            this.page += 1;
                            this.posts.push(...data.posts.data);

                            $state.loaded();
                        } else {
                            $state.complete();
                        }

                        if (isEmpty($state)) {
                            NProgress.inc();
                        }
                    })
                    .catch(() => {
                        NProgress.done();
                    });
            }
        },

        changeType() {
            this.page = 1;
            this.posts = [];
            this.infiniteId += 1;
        },
    },
};
</script>

<style scoped>
#featuredImage {
    background-size: cover;
    width: 57px;
    height: 57px;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
    border-radius: 50%;
}
</style>
