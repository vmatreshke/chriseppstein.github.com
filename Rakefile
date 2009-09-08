require 'active_support'

def ok_failed(condition)
  if (condition)
    puts "OK"
  else
    puts "FAILED"
  end
end

port = "4000"
site = "_site"

desc "list tasks"
task :default do
  puts "Tasks: #{(Rake::Task.tasks - [Rake::Task[:default]]).to_sentence}"
  puts "(type rake -T for more detail)\n\n"
end

desc "remove files in output directory"
task :clean do
  puts "Removing output..."
  Dir["#{site}/*"].each { |f| rm_rf(f) }
end

desc "generate website in output directory"
task :generate => :clean do
  puts "Generating website..."
  system "compass"
  system "jekyll"
  Dir["#{site}/stylesheets/*.sass"].each { |f| rm_rf(f) }
  system "mv _site/blog/atom.html _site/blog/atom.xml"
end

desc "generate and deploy website"
task :deploy => :generate do
  print "Deploying website..."
  ok_failed system("rsync -avz --delete #{site}/ mathisweb@imathis.com:~/brandonmathis.com/")
end

desc "start up an instance of serve on the output files"
task :start_serve => :stop_serve do
  cd "#{site}" do
    print "Starting serve..."
    ok_failed system("serve #{port} > /dev/null 2>&1 &")
  end
end

desc "stop all instances of serve"
task :stop_serve do
  pid = `ps auxw | awk '/bin\\/serve\\ #{port}/ { print $2 }'`.strip
  if pid.empty?
    puts "Serve is not running"
  else
    print "Stoping serve..."
    ok_failed system("kill -9 #{pid}")
  end
end

desc "preview the site in a web browser"
multitask :preview => [:generate, :start_serve] do
  system "open http://localhost:#{port}"
end

def rebuild_site(relative)
  puts ">>> Change Detected to: #{relative} <<<"
  IO.popen('rake generate') do |io|
    if io
      begin
        print io.read(512)
        sleep 0.25
      end until io.eof?
    else
      puts "no io"
    end
  end
  puts '>>> Update Complete <<<'
end

desc "Watch the site and regenerate when it changes"
task :watch do
  require 'fssm'
  puts ">>> Watching for Changes <<<"
  FSSM.monitor("#{File.dirname(__FILE__)}/_source", '**/*') do
    update {|base, relative| rebuild_site(relative)}
    delete {|base, relative| rebuild_site(relative)}
    create {|base, relative| rebuild_site(relative)}
  end
end