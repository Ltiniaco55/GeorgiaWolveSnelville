import { onBeforeUnmount, reactive } from 'vue';

/**
 * Damped spring stepped once per animation frame, for several values at once.
 *
 * Each frame: velocity = (velocity + (target - value) * stiffness) * damping,
 * then value += velocity. The loop stops by itself when every value has settled
 * and is cancelled when the component unmounts.
 *
 * @template {Record<string, number>} T
 * @param {T} initialValues
 * @param {{ stiffness?: number, damping?: number, precision?: number }} [options]
 * @returns {{ values: T, setTarget: (targets: Partial<T>) => void, reset: (targets?: Partial<T>) => void }}
 */
export function useSpring(initialValues, { stiffness = 0.055, damping = 0.86, precision = 0.001 } = {}) {
    const values = reactive({ ...initialValues });
    const targets = { ...initialValues };
    const velocities = Object.fromEntries(Object.keys(initialValues).map((key) => [key, 0]));
    let frame = null;

    function step() {
        let isMoving = false;

        for (const key of Object.keys(targets)) {
            velocities[key] = (velocities[key] + (targets[key] - values[key]) * stiffness) * damping;
            values[key] += velocities[key];

            if (Math.abs(velocities[key]) > precision || Math.abs(targets[key] - values[key]) > precision) {
                isMoving = true;
            } else {
                values[key] = targets[key];
                velocities[key] = 0;
            }
        }

        frame = isMoving ? requestAnimationFrame(step) : null;
    }

    /**
     * Moves the spring towards new targets.
     *
     * @param {Partial<T>} newTargets
     */
    function setTarget(newTargets) {
        Object.assign(targets, newTargets);
        frame ??= requestAnimationFrame(step);
    }

    /**
     * Jumps straight to the given values (initial values by default) without animating.
     *
     * @param {Partial<T>} [newValues]
     */
    function reset(newValues = initialValues) {
        if (frame !== null) {
            cancelAnimationFrame(frame);
            frame = null;
        }

        Object.assign(targets, newValues);
        Object.assign(values, newValues);
        Object.keys(velocities).forEach((key) => {
            velocities[key] = 0;
        });
    }

    onBeforeUnmount(() => {
        if (frame !== null) {
            cancelAnimationFrame(frame);
        }
    });

    return { values, setTarget, reset };
}
