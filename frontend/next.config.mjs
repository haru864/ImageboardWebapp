/** @type {import('next').NextConfig} */
const nextConfig = {
    output: 'export',
    env: {
        domain: 'http://imageboard.test.com'
    },
    basePath: '/nextjs',
};

export default nextConfig;
