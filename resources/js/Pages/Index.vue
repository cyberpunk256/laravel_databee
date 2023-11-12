<script setup>
import MapLayout from '@/Layouts/MapLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <MapLayout>
    <template v-slot:map>
      <div id="map" class="fix_map"></div>
    </template>
    <template v-slot:action>
      <Link href="/admin/media" as="div">
        <v-btn icon="mdi-arrow-left" size="large"></v-btn>
      </Link>
    </template>
    <template v-slot:content>
      <v-dialog v-if="modal_type == 1 && modal_video_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <three-video-player 
            :url="modal_video_url"
            :id="modal_id"
          />
        </v-card>
      </v-dialog>
      <v-dialog v-if="modal_type == 2 && modal_image_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <v-img v-if="modal" max-width="1000" contain :src="modal_image_url"></v-img>
          </div>
        </v-card>
      </v-dialog>
      <v-dialog v-if="modal_type == 3 && modal_image_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <panorama v-if="modal"  :url="modal_image_url"></panorama>
          </div>
        </v-card>
      </v-dialog>
    </template>
  </MapLayout>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Panorama.vue';

export default {
  components: { ThreeVideoPlayer, Panorama },
  props: ['type', 'records'],
  data() {
    return {
      map: null,
      map_default_option: {
        view: [0,0],
        pin: [0,0],
        scale: 1
      },
      modal: false,
      modal_type: 1, // video
      modal_video_url: null,
      modal_image_url: null,
      bounds_sum: new L.LatLngBounds(),
      loaded_gpxs: 0,
      gpxOptions: {
        async: true,
        marker_options: {
            startIconUrl: false,
            endIconUrl: false,
            shadowUrl: false,
        },
        gpx_options: {
          joinTrackSegments: false
        }, 
      }
    };
  },
  mounted() {
    const self = this
    self.map_default_option = this.constant.map
    self.map = L.map('map').setView(self.map_default_option.view,self.map_default_option.zoom);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    }).addTo(self.map)
    for (let i = 0; i < self.records.length; i++) {
      const record = self.records[i];
      if(record.type == 1) { // video
        const gpx_url = self.get_path_url(record.gpx_path)
        console.log('gpx_url', gpx_url)
        new L.GPX(gpx_url, this.gpxOptions)
          .on('loaded', self.onGpxLoaded)
          .on('click', function() {
              self.onShowModal(record)
          });
      } else if(record.type == 2 || record.type == 3) {
        const coordinate = (record.image_lat && record.image_long) ? 
          [record.image_lat, record.image_long] : this.map_default_option.pin
        
        const pin_marker = L.marker(coordinate, { draggable: true })
          .addTo(self.map)
          .on('click', function() {
            self.onShowModal(record)
          });
        this.bounds_sum.extend(coordinate)
        this.loaded_gpxs += 1
      }
    }
  },
  watch: {
    loaded_gpxs(new_loaded_gpxs) {
      if(new_loaded_gpxs == this.records.length) {
        this.map.fitBounds(this.bounds_sum);
      }
    },
  },
  methods: {
    onGpxLoaded(e) {
      const gpxLayer = e.target;
      this.bounds_sum.extend(gpxLayer.getBounds());
      gpxLayer.addTo(this.map);
      this.loaded_gpxs += 1
    },
    onShowModal(record) {
      this.modal_id = record.id
      this.modal_type = record.type
      if(record.type == 1) {
        this.modal_video_url = this.get_path_url(record.media_path)
        this.modal = true
      } else { // image, panorama
        this.modal_image_url = this.get_path_url(record.media_path)
        this.modal = true
      }
    }
  },
};
</script>