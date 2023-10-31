<template>
    <div style="display: none">
        <slot v-if="ready"></slot>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import L from 'leaflet';
import 'leaflet-gpx';

export default {
    props: {
        gpxFile: {
            type: String,
        },
        gpxOptions: {
            type: Object,
            default: () => ({ async: true }),
        },
        visible: {
            type: Boolean,
            default: true,
        },
    },
    watch: {
        visible(newValue) {
            if (newValue) {
                this.map.addLayer(this);
            } else {
                this.map.leafletObject.removeLayer(this);
            }
        },
        gpxFile() {
            this.setup();
        },
        gpxOptions() {
            this.setup();
        },
    },
    data() {
        return {
            ready: false,
            gpx: null as null | L.GPX,
            map: null 
        };
    },
    mounted() {
        this.map = this.$parent.leafletObject
        this.setup();
    },
    beforeDestroy() {
        this.map.removeLayer(this);
    },
    methods: {
        setup() {
            if (this.gpx) {
                this.map.removeLayer(this.gpx);
                // @ts-ignore
                L.DomEvent.off(this.gpx, this.$attrs);
            }
            this.gpx = new L.GPX(this.$props.gpxFile, this.$props.gpxOptions)
                .on('loaded', this.gpxLoaded)
                .on('addpoint', this.addpoint)
                .on('addline', this.addline);
                
                
            console.log('this.gpx', this.gpx);
            L.DomEvent.on(this.gpx, this.$attrs);
            this.ready = true;
            
            this.map.addLayer(this.gpx);
        },
        gpxLoaded(loadedEvent: L.LeafletEvent) {
            this.$emit('gpx-loaded', loadedEvent);
        },
        addpoint(addPointEvent: L.LeafletEvent) {
            this.$emit('addpoint', addPointEvent);
        },
        addline(addLineEvent: L.LeafletEvent) {
            this.$emit('addline', addLineEvent);
        },
    },
}
</script>