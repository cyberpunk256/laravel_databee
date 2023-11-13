<template>
    <div class="pa-8">
        <v-row>
            <v-data-table-server
            v-if="show_table"
            ref="table"
            v-model="selectedRecords"
            :items="captureData.data"
            :items-length="captureData.total"
            :headers="headers"
            :search="search"
            class="elevation-0"
            :loading="isLoading"
            @update:options="onLoadCaptureRecords"
            show-select
            >
                <template #[`item.url`]="{ item }">
                    <div class="pa-2">
                        <v-img 
                        :src="get_path_url(item.raw.url)"
                        width="auto" 
                        max-width="80" 
                        max-height="80"
                        cover
                        ></v-img>
                    </div>
                </template>
                <template #[`item.updated_at`]="{ item }">
                    {{ getYmdHiFromDTS(item.raw.updated_at) }}
                </template>
            </v-data-table-server>
        </v-row>
        <v-row>
            <v-col cols="auto">
                <v-btn 
                    :disabled="selectedRecords.length == 0" 
                    @click="onRemoveConfirm" 
                    color="red" 
                hide-details>選択したメディを削除</v-btn>
            </v-col>
            <v-spacer></v-spacer>
            <v-col cols="auto">
                <v-btn 
                    :disabled="selectedRecords.length == 0" 
                    @click="onDownload" 
                    color="primary" 
                hide-details>選択したメディをダウンロード</v-btn>
            </v-col>
        </v-row>
        <v-dialog v-model="deleteDialog" persistent width="auto">
            <v-card>
            <v-card-text>本当に削除しますか？</v-card-text>
            <v-divider></v-divider>
            <v-row class="my-0" justify="center">
                <v-col cols="auto">
                    <v-btn @click="deleteDialog = false">キャンセル</v-btn>
                </v-col>
                <v-col cols="auto">
                    <v-btn color="red" :loading="isLoading" text @click="onRemoveSelected">削除</v-btn>
                </v-col>
            </v-row>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import JSZip from 'jszip';

export default {
    data() {
        return {
            isLoading: false,
            deleteDialog: false,
            selectedRecords: [],
            show_table: true,
            search: null,
            headers: [
                { title: 'ID', key: 'id', sortable: false },
                { title: 'キャプチャ', key: 'url', sortable: false },
                { title: '取得日', key: 'updated_at', sortable: false },
            ],
            captureData: {
                data: [],
                total: 0
            }
        }
    },
    mounted() {
    },
    methods: {
        async onLoadCaptureRecords({ page, itemsPerPage, sortBy, search }) {
            try {
                this.isLoading = true
                var params = {
                    page: page,
                    limit: itemsPerPage,
                    sort: sortBy[0],
                }
                if (search) {
                    params.search = search
                }
                const { data } = await axios.get('/api/capture', { params: params })
                this.captureData = data
            } catch(e) {
                console.log(e)
                this.show_toast()
            } finally {
                this.isLoading = false
            }
        },
        async onRemoveSelected() {
            const self = this
            try {
                this.deleteDialog = false
                this.isLoading = true
                const { data } = await axios.post(`/api/capture/delete_records`, {
                    ids: this.selectedRecords
                })
                if(data.success) {
                    this.show_toast("success", data.success)
                    this.show_table = false
                    this.$nextTick(() => {
                        self.show_table = true
                    })
                }
            } catch(e) {
                console.log(e)
                this.show_toast()
            } finally {
                this.isLoading = false
            }
        },
        onRemoveConfirm() {
            this.deleteDialog = true
        },
        async onDownload() {
            const self = this
            const zip = new JSZip();

            // 画像ファイルのURL一覧からZIPに追加
            await Promise.all(this.captureData.data.map(async (capture, index) => {
                const response = await fetch(self.get_path_url(capture.url));
                const imageData = await response.blob();
                zip.file(`${capture.id}.jpg`, imageData);
            }));

            // ZIPファイルを生成
            const content = await zip.generateAsync({ type: 'blob' });

            // ZIPファイルをダウンロード
            const link = document.createElement('a');
            link.href = URL.createObjectURL(content);
            link.download = 'captures.zip';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
}
</script>