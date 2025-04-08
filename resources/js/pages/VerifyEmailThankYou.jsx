import React from 'react';
import { motion } from 'framer-motion';

export default function VerifyEmailThankYou() {
    return (
        <div style={styles.page}>
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, ease: "easeOut" }}
                style={styles.container}
            >
                <div style={styles.glowEffect}></div>

                <svg style={styles.icon} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <h1 style={styles.title}>
                    <span style={styles.gradientText}>Weryfikacja zakończona!</span>
                </h1>

                <p style={styles.text}>Twój adres e-mail został potwierdzony. Możesz teraz korzystać z pełnej funkcjonalności aplikacji.</p>

                <motion.div
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    style={styles.button}
                >
                    Przejdź do dashboardu
                </motion.div>

                <div style={styles.footer}>
                    <p style={styles.footerText}>Dziękujemy za zaufanie!</p>
                </div>
            </motion.div>
        </div>
    );
}

const styles = {
    page: {
        backgroundColor: '#0a0a0a',
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '20px',
        background: 'radial-gradient(circle at 10% 20%, rgba(41, 39, 85, 0.8) 0%, rgba(10,10,10,1) 90%)'
    },
    container: {
        backgroundColor: '#121212',
        padding: '50px 40px',
        borderRadius: '16px',
        boxShadow: '0 10px 25px rgba(0, 0, 0, 0.3)',
        maxWidth: '600px',
        width: '100%',
        textAlign: 'center',
        position: 'relative',
        overflow: 'hidden',
        border: '1px solid rgba(255, 255, 255, 0.05)'
    },
    glowEffect: {
        position: 'absolute',
        top: '-50%',
        left: '-50%',
        width: '200%',
        height: '200%',
        background: 'radial-gradient(circle, rgba(138, 43, 226, 0.15) 0%, transparent 70%)',
        animation: 'rotate 15s linear infinite',
    },
    icon: {
        width: '80px',
        height: '80px',
        margin: '0 auto 20px',
        color: '#8a2be2',
        filter: 'drop-shadow(0 0 10px rgba(138, 43, 226, 0.5))'
    },
    title: {
        fontSize: '2.5rem',
        marginBottom: '25px',
        fontWeight: '700',
        lineHeight: '1.2',
        background: 'linear-gradient(90deg, #8a2be2, #00bfff)',
        WebkitBackgroundClip: 'text',
        WebkitTextFillColor: 'transparent',
        backgroundClip: 'text',
        textFillColor: 'transparent'
    },
    gradientText: {
        background: 'linear-gradient(90deg, #8a2be2, #00bfff)',
        WebkitBackgroundClip: 'text',
        WebkitTextFillColor: 'transparent',
        backgroundClip: 'text',
    },
    text: {
        fontSize: '1.1rem',
        color: 'rgba(255, 255, 255, 0.85)',
        lineHeight: '1.6',
        marginBottom: '40px',
        maxWidth: '80%',
        marginLeft: 'auto',
        marginRight: 'auto'
    },
    button: {
        background: 'linear-gradient(135deg, #8a2be2 0%, #00bfff 100%)',
        color: 'white',
        padding: '16px 32px',
        borderRadius: '50px',
        fontSize: '1rem',
        fontWeight: '600',
        cursor: 'pointer',
        border: 'none',
        outline: 'none',
        margin: '0 auto',
        width: 'fit-content',
        boxShadow: '0 4px 15px rgba(138, 43, 226, 0.4)',
        transition: 'all 0.3s ease'
    },
    footer: {
        marginTop: '40px',
        paddingTop: '20px',
        borderTop: '1px solid rgba(255, 255, 255, 0.1)'
    },
    footerText: {
        color: 'rgba(255, 255, 255, 0.6)',
        fontSize: '0.9rem'
    },
    '@keyframes rotate': {
        from: { transform: 'rotate(0deg)' },
        to: { transform: 'rotate(360deg)' }
    }
};