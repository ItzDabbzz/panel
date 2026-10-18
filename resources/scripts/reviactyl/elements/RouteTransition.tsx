import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import type { ReactNode } from 'react';
import { useLocation } from 'react-router-dom';

interface Props {
    children: ReactNode;
    className?: string;
}

const RouteTransition = ({ children, className }: Props) => {
    const location = useLocation();
    const reduceMotion = useReducedMotion();
    const transition = reduceMotion ? { duration: 0 } : { duration: 0.18, ease: 'easeOut' as const };

    return (
        <AnimatePresence mode='wait' initial={false}>
            <motion.div
                key={location.pathname}
                className={className}
                initial={{ opacity: 0, y: reduceMotion ? 0 : 6 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: reduceMotion ? 0 : -4 }}
                transition={transition}
            >
                {children}
            </motion.div>
        </AnimatePresence>
    );
};

export default RouteTransition;
