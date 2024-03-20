/** @type {import('next').NextConfig} */
const nextConfig = {
    output: 'export',
    env: {
        // frontDomain: 'http://imageboard.test.com',
        frontDomain: 'http://localhost:3000',
        apiDomain: 'http://imageboard.test.com'
    },
    // basePath: '/nextjs',
};

export default nextConfig;
