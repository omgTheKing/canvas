<template>
    <section>
        <page-header>
            <template slot="status">
                <ul class="navbar-nav mr-auto flex-row float-right">
                    <li class="text-muted font-weight-bold">
                        <div class="border-left pl-3 d-flex align-items-center" style="gap: 10px">
                            <div v-if="!isSaving && !isSaved">
                                <span v-if="isPublished(post.approved_at)">Yayında</span>
                                <span v-if="!isPublished(post.approved_at) && isPublished(post.published_at)"
                                    >Yayına sunuldu</span
                                >
                                <span v-if="isDraft(post.published_at)">{{ trans.draft }}</span>
                            </div>
                            <span v-if="isSaving">{{ trans.saving }}</span>
                            <span v-if="isSaved" class="text-success">{{ trans.saved }}</span>

                            <span
                                v-if="!editDisabled && isPublished(post.published_at)"
                                class="btn btn-danger"
                                @click="savePost(true)"
                                >Kaydet</span
                            >
                        </div>
                    </li>
                </ul>
            </template>

            <template
                slot="options"
                v-if="isEditor || isAdmin || (isContributor ? !isPublished(post.approved_at) : false)"
            >
                <div class="dropdown">
                    <a
                        id="navbarDropdown"
                        class="nav-link pr-0"
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
                        <router-link
                            v-if="isPublished(post.approved_at)"
                            :to="{ name: 'post-stats', params: { id: uri } }"
                            class="dropdown-item"
                        >
                            {{ trans.view_stats }}
                        </router-link>
                        <div v-if="isPublished(post.approved_at)" class="dropdown-divider" />
                        <a
                            v-if="isDraft(post.published_at)"
                            href="#"
                            class="dropdown-item"
                            @click="updatePublishedAt()"
                        >
                            Yayına Sun
                        </a>
                        <a
                            v-if="
                                isPublished(post.published_at) &&
                                isDraft(post.approved_at) &&
                                (isAdmin || (isEditor && post.blogger_id != user.id))
                            "
                            href="#"
                            class="dropdown-item"
                            @click="updateApprovedAt()"
                        >
                            Yayınla
                        </a>
                        <a v-if="postStatus === 2" href="#" class="dropdown-item" @click="convertToDraft()">
                            {{ trans.convert_to_draft }}
                        </a>
                        <a
                            v-if="postStatus === 3 && (isAdmin || isEditor)"
                            href="#"
                            class="dropdown-item"
                            @click="convertToDraft()"
                        >
                            {{ trans.convert_to_draft }}
                        </a>
                        <a v-if="!editDisabled" href="#" class="dropdown-item" @click="showSettingsModal">
                            {{ trans.general_settings }}
                        </a>
                        <a v-if="!editDisabled" href="#" class="dropdown-item" @click="showFeaturedImageModal">
                            {{ trans.featured_image }}
                        </a>
                        <a v-if="!editDisabled" href="#" class="dropdown-item" @click="showSeoModal">
                            {{ trans.seo_settings }}
                        </a>
                        <a
                            v-if="!creatingPost && (isAdmin || !isPublished(post.published_at))"
                            href="#"
                            class="dropdown-item text-danger"
                            @click="showDeleteModal"
                        >
                            {{ trans.delete }}
                        </a>
                    </div>
                </div>
            </template>
        </page-header>

        <main v-if="isReady" class="py-4">
            <div class="col-xl-8 offset-xl-2 col-lg-10 offset-lg-1 col-md-12">
                <div class="form-group my-3" v-if="editDisabled">
                    <div class="alert alert-danger w-100">
                        <span v-if="isContributor">Bu gönderi yayınlandığı için düzenleyemezsiniz!</span>
                        <a
                            v-else-if="isAdmin || post.blogger_id != user.id"
                            @click="editDisabled = false"
                            style="cursor: pointer"
                            >Düzenleme kilidini aç</a
                        >
                        <span v-else
                            >Kendi gönderinizi yayına sunulduktan/yayınlandıktan sonra düzenleyemezsiniz. Taslağa
                            döndürüp tekrar deneyin.</span
                        >
                    </div>
                </div>
                <div class="form-group my-3" v-if="!isEmpty(errors)">
                    <div class="alert alert-danger w-100">
                        <span>{{ errors[0] }}</span>
                    </div>
                </div>
                <div class="form-group my-3" v-if="!editDisabled">
                    <textarea-autosize
                        v-model="post.title"
                        :placeholder="trans.title"
                        style="font-size: 42px"
                        class="w-100 form-control-lg border-0 font-serif bg-transparent px-0"
                        rows="1"
                        maxlength="90"
                        @input.native="updatePost"
                    />
                </div>
                <div class="form-group my-3" v-else>
                    <h1
                        style="font-size: 42px"
                        class="w-100 form-control-lg border-0 font-serif bg-transparent px-0"
                        rows="1"
                    >
                        {{ post.title }}
                    </h1>
                </div>

                <div class="form-group my-2">
                    <quill-editor :key="post.uuid" :post="post" :disabled="editDisabled" @update-post="savePost" />
                </div>
            </div>
        </main>

        <section v-if="isReady">
            <settings-modal
                ref="settingsModal"
                :post="post"
                :tags="tags"
                :topics="topics"
                :errors="errors"
                @sync-slug="updateSlug"
                @add-tag="addTag"
                @add-post-tag="addPostTag"
                @add-post-topic="addPostTopic"
                @add-topic="addTopic"
                @update-post="savePost"
            />
            <featured-image-modal
                ref="featuredImageModal"
                :post="post"
                @update-featured-image="updateFeaturedImage"
                @remove-featured-image="removeFeaturedImage"
                @update-post="savePost"
            />
            <seo-modal
                ref="seoModal"
                :post="post"
                @sync-title="updateMetaTitle"
                @sync-description="updateMetaDescription"
                @update-post="savePost"
            />
            <delete-modal
                ref="deleteModal"
                :header="trans.delete"
                :message="trans.deleted_posts_are_gone_forever"
                @delete="deletePost"
            />
        </section>
    </section>
</template>

<script>
import { mapGetters, mapState } from 'vuex';
import $ from 'jquery';
import DeleteModal from '../components/modals/DeleteModal';
import FeaturedImageModal from '../components/modals/FeaturedImageModal';
import NProgress from 'nprogress';
import PageHeader from '../components/PageHeader';
import PublishModal from '../components/modals/PublishModal';
import QuillEditor from '../components/editor/QuillEditor';
import SeoModal from '../components/modals/SeoModal';
import SettingsModal from '../components/modals/SettingsModal';
import Vue from 'vue';
import VueTextAreaAutosize from 'vue-textarea-autosize';
import debounce from 'lodash/debounce';
import get from 'lodash/get';
import isEmpty from 'lodash/isEmpty';
import status from '../mixins/status';
import moment from 'moment';

Vue.use(VueTextAreaAutosize);

export default {
    name: 'edit-post',

    components: {
        PublishModal,
        FeaturedImageModal,
        DeleteModal,
        QuillEditor,
        PageHeader,
        SeoModal,
        SettingsModal,
    },

    mixins: [status],

    data() {
        return {
            uri: this.$route.params.id || 'create',
            post: {
                id: null,
                title: null,
                slug: null,
                summary: null,
                body: null,
                published_at: null,
                approved_at: null,
                featured_image: null,
                featured_image_caption: null,
                meta: {
                    description: null,
                    title: null,
                    canonical_link: null,
                },
                tags: [],
                topic: [],
            },
            tags: [],
            topics: [],
            isSaving: false,
            isSaved: false,
            errors: [],
            isReady: false,
            editDisabled: true,
        };
    },

    computed: {
        ...mapGetters({
            trans: 'settings/trans',
            isAdmin: 'settings/isAdmin',
            isEditor: 'settings/isEditor',
            isContributor: 'settings/isContributor',
        }),

        user() {
            return this.$store.state.settings.user;
        },

        creatingPost() {
            return this.$route.name === 'create-post';
        },

        postStatus() {
            if (this.creatingPost) {
                return 0;
            }
            if (this.isDraft(this.post.published_at)) {
                return 1;
            }
            if (this.isPublished(this.post.published_at)) {
                return 2;
            }
            return 3;
        },
    },

    watch: {
        async $route(to) {
            if (this.uri === 'create' && to.params.id === this.id) {
                this.uri = to.params.id;
            }

            if (this.uri !== to.params.id) {
                this.isReady = false;
                this.uri = to.params.id;
                await Promise.all([this.fetchPost()]);
                this.isReady = true;
                NProgress.done();
            }
        },
    },

    async created() {
        await Promise.all([this.fetchPost()]);
        this.isReady = true;
        NProgress.done();
    },

    methods: {
        fetchPost() {
            return this.request()
                .get(`/api/posts/${this.uri}`)
                .then(({ data }) => {
                    this.post.id = data.post.id;
                    this.post.uuid = data.post.uuid;
                    this.post.title = get(data.post, 'title', '');
                    this.post.slug = get(data.post, 'slug', '');
                    this.post.summary = get(data.post, 'summary', '');
                    this.post.body = get(data.post, 'body', '');
                    this.post.published_at = get(data.post, 'published_at', '');
                    this.post.approved_at = get(data.post, 'approved_at', '');
                    this.post.featured_image = get(data.post, 'featured_image', '');
                    this.post.featured_image_caption = get(data.post, 'featured_image_caption', '');
                    this.post.meta.description = get(data.post.meta, 'description', '');
                    this.post.meta.title = get(data.post.meta, 'title', '');
                    this.post.meta.canonical_link = get(data.post.meta, 'canonical_link', '');
                    this.post.tags = get(data.post, 'tags', []);
                    this.post.topic = get(data.post, 'topic', []);
                    this.post.blogger_id = get(data.post, 'blogger_id', null);

                    this.tags = get(data, 'tags', []);
                    this.topics = get(data, 'topics', []);
                    this.editDisabled = !!this.post.published_at;

                    NProgress.inc();
                })
                .catch(() => {
                    this.$router.push({ name: 'posts' });
                });
        },

        convertToDraft() {
            this.post.published_at = null;
            this.post.approved_at = null;
            this.editDisabled = false;
            this.savePost();
        },

        updatePublishedAt() {
            this.post.published_at = moment().utc().format('YYYY-MM-DD HH:mm:ss');
            this.editDisabled = true;
            this.savePost(true);
        },

        async updateApprovedAt() {
            this.post.approved_at = moment().utc().format('YYYY-MM-DD HH:mm:ss');
            await this.savePost(true);
            this.editDisabled = true;
        },

        updateSlug(slug) {
            this.post.slug = slug;
        },

        addTag(tag) {
            this.tags.push(tag);
        },

        addTopic(topic) {
            this.topics.push([topic]);
        },

        addPostTag(tag) {
            this.post.tags.push(tag);
            this.savePost();
        },

        addPostTopic(topic) {
            this.post.topic = [topic];
            this.savePost();
        },

        updateFeaturedImage(path) {
            this.post.featured_image = path;
        },

        removeFeaturedImage() {
            this.post.featured_image = null;
            this.post.featured_image_caption = null;
        },

        updateMetaTitle(title) {
            this.post.meta.title = title;
        },

        updateMetaDescription(description) {
            this.post.meta.description = description;
        },

        updatePost: debounce(function () {
            this.savePost();
        }, 3000),

        async savePost(force = false) {
            if (!isEmpty(this.post.published_at) && !force) {
                return;
            }
            this.errors = [];
            this.isSaving = true;
            this.isSaved = false;
            this.post.title = this.post.title || 'Title';

            const post = this.post;
            if (this.isContributor) {
                delete post.approved_at;
            }
            await this.request()
                .post(`/api/posts/${this.post.uuid}`, this.post)
                .then(({ data }) => {
                    this.isSaving = false;
                    this.isSaved = true;
                    this.post = data;

                    // TODO: Check if searchable data is changing
                    this.$store.dispatch('search/buildIndex', true);
                })
                .catch((error) => {
                    this.errors = error.response.data.errors || [error.response.data.message];
                });

            if (isEmpty(this.errors) && this.creatingPost) {
                await this.$router.push({ name: 'edit-post', params: { id: this.post.uuid } });
                NProgress.done();
            }
            if (!isEmpty(this.errors) && !this.creatingPost) {
                await this.fetchPost();
                NProgress.done();
            }

            setTimeout(() => {
                this.isSaved = false;
                this.isSaving = false;
            }, 3000);
        },

        async deletePost() {
            await this.request()
                .delete(`/api/posts/${this.post.uuid}`)
                .then(() => {
                    this.$store.dispatch('search/buildIndex', true);
                    this.$toasted.show(this.trans.success, {
                        className: 'bg-success',
                    });
                });

            $(this.$refs.deleteModal.$el).modal('hide');

            await this.$router.push({ name: 'posts' });
        },

        showPublishModal() {
            $(this.$refs.publishModal.$el).modal('show');
        },

        showSettingsModal() {
            $(this.$refs.settingsModal.$el).modal('show');
        },

        showFeaturedImageModal() {
            $(this.$refs.featuredImageModal.$el).modal('show');
        },

        showSeoModal() {
            $(this.$refs.seoModal.$el).modal('show');
        },

        showDeleteModal() {
            $(this.$refs.deleteModal.$el).modal('show');
        },
        isEmpty,
    },
};
</script>
