# 2.0.0
- Drop support for php 5.6.
- Delete deprecated BaseKernel and fix corresponding unit tests.

# 1.5.0
- Changed the order of the 'named' constructor parameter to support backwards compatibility with Symfony creating kernels too.

# 1.4.0
- Add a feature where the kernel can be 'named' via a constructor parameter. 
  This way one app can support multiple types of kernels. See doc/ for more info.


# 1.3.0
- Maintenance release containing only CS and dependency fixes
