/**
 * A small self-contained 3D viewer for one home-page section (Filament → Home page sections → 3D model).
 * One renderer per visible section; Components/home/Section3D.vue creates it when the section scrolls
 * into view and disposes it when it leaves, so only a couple of WebGL contexts exist at any time.
 */
import * as THREE from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { loadCustom, readPalette, webglAvailable } from './scene';
import { buildMixed, buildModel, disposeObject, MODEL_KEYS, type BuiltModel, type ModelKey } from './models';

export interface SectionViewerOptions {
    /** a ModelKey, 'mixed' or 'custom' */
    model: string;
    customUrl?: string | null;
}

export interface SectionViewer {
    dispose(): void;
}

/** browsers allow ~8-16 WebGL contexts; the page background uses one */
const MAX_LIVE = 3;
let live = 0;

export async function createSectionViewer(canvas: HTMLCanvasElement, options: SectionViewerOptions): Promise<SectionViewer | null> {
    if (live >= MAX_LIVE || !webglAvailable()) return null;
    live++;

    let disposed = false;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: 'low-power' });
    renderer.setClearColor(0x000000, 0);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;

    const scene = new THREE.Scene();
    const pmrem = new THREE.PMREMGenerator(renderer);
    const environment = pmrem.fromScene(new RoomEnvironment(), 0.04);
    scene.environment = environment.texture;

    const palette = readPalette();
    scene.add(new THREE.HemisphereLight(0xffffff, 0x334155, 0.7));
    const key = new THREE.DirectionalLight(0xffffff, 2.4);
    key.position.set(4, 6, 7);
    scene.add(key);
    const rim = new THREE.DirectionalLight(palette.secondary, 2.2);
    rim.position.set(-6, 2, -4);
    scene.add(rim);
    const fill = new THREE.PointLight(palette.primary, 30, 30);
    fill.position.set(3, -2, 5);
    scene.add(fill);

    const camera = new THREE.PerspectiveCamera(30, 1, 0.1, 100);
    camera.position.set(0, 0.4, 9.5);

    const tilt = new THREE.Group();
    const spinner = new THREE.Group();
    tilt.add(spinner);
    scene.add(tilt);

    let built: BuiltModel | null = null;

    if (options.model === 'custom') {
        built = options.customUrl ? await loadCustom(options.customUrl) : null;
    } else if (options.model === 'mixed') {
        built = buildMixed(palette);
        built.group.scale.setScalar(0.72);
    } else if ((MODEL_KEYS as string[]).includes(options.model)) {
        built = buildModel(options.model as ModelKey, palette);
    }

    const cleanup = () => {
        disposeObject(scene);
        environment.dispose();
        pmrem.dispose();
        renderer.dispose();
        renderer.forceContextLoss();
        live = Math.max(0, live - 1);
    };

    if (disposed || !built) {
        cleanup();

        return null;
    }

    spinner.add(built.group);
    const model = built;

    // -- size follows the canvas box ------------------------------------------------
    const resize = () => {
        const w = canvas.clientWidth;
        const h = canvas.clientHeight;
        if (!w || !h) return;

        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    };
    const observer = new ResizeObserver(() => {
        resize();
        running || frame();
    });
    observer.observe(canvas);
    resize();

    // -- loop -----------------------------------------------------------------------
    const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
    const onPointer = (e: PointerEvent) => {
        pointer.tx = (e.clientX / window.innerWidth) * 2 - 1;
        pointer.ty = (e.clientY / window.innerHeight) * 2 - 1;
    };

    let last = performance.now();
    let elapsed = 0;
    let raf = 0;
    let running = false;

    function frame(): void {
        const now = performance.now();
        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;
        elapsed += dt;

        spinner.rotation.y += 0.32 * dt;
        model.tick?.(elapsed, dt);

        pointer.x += (pointer.tx - pointer.x) * Math.min(1, dt * 3);
        pointer.y += (pointer.ty - pointer.y) * Math.min(1, dt * 3);
        tilt.rotation.y = pointer.x * 0.3;
        tilt.rotation.x = pointer.y * 0.15;
        tilt.position.y = Math.sin(elapsed * 0.9) * 0.1;

        renderer.render(scene, camera);

        if (running) raf = requestAnimationFrame(frame);
    }

    const start = () => {
        if (running || reducedMotion || disposed) return;
        running = true;
        last = performance.now();
        raf = requestAnimationFrame(frame);
    };
    const stop = () => {
        running = false;
        cancelAnimationFrame(raf);
    };
    const onVisibility = () => (document.hidden ? stop() : start());

    window.addEventListener('pointermove', onPointer, { passive: true });
    document.addEventListener('visibilitychange', onVisibility);

    frame(); // first still frame (also what reduced-motion visitors see)
    start();

    return {
        dispose() {
            if (disposed) return;
            disposed = true;
            stop();
            observer.disconnect();
            window.removeEventListener('pointermove', onPointer);
            document.removeEventListener('visibilitychange', onVisibility);
            cleanup();
        },
    };
}
